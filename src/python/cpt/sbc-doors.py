from gpio import *
from time import *
from realhttp import *
import json

# CONFIGURAÇÕES DA API
PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"
TOKEN = "Bearer 5b57634f8e96f1c24ae7748f6069e5cf"

http = RealHTTPClient()

# MAPEAMENTO DE HARDWARE

# Pinos de COMUNICAÇÃO (Saída para outros MCUs)
# Nestes pinos, o SBC escreve comandos ("OPEN:office", "VALID:office", etc.)
MCU_DOOR_CMD_PIN = 0  # Comandos para o MCU das Portas
MCU_RFID_CMD_PIN = 1  # Comandos para o MCU das Luzes RFID

# Pinos de SENSORES (Entrada dos Leitores RFID)
# Mapeamento: ID do leitor -> Pino onde o leitor está ligado no SBC
RFID_SENSORS = {
    "main-entrance": 2,
    "office": 3,
    "locker-room-male": 4,
    "locker-room-female": 5,
}

# VARIÁVEIS DE ESTADO

# Regista o último cartão lido por cada leitor para evitar leituras repetidas (spam)
# Exemplo: {'office': 1024, 'main-entrance': 0}
last_seen = {reader_id: 0 for reader_id in RFID_SENSORS.keys()}

# Filas de comandos pendentes para enviar aos MCUs
pending_rfid_cmd = None
pending_door_cmd = None

# LÓGICA DE LEITURA (RFID)

def detect_card_hover(reader_id, pin):
    """
    Verifica se há um cartão sobre um leitor específico.
    Se houver um novo cartão, inicia o processo de scan.
    """
    raw_value = customRead(pin)

    # 1. Proteção contra dados inválidos (None ou vazios)
    if raw_value is None:
        return

    raw_value = str(raw_value).strip()
    
    # 2. Se não for número válido, reseta o estado (nenhum cartão)
    if raw_value == "" or not raw_value.isdigit():
        last_seen[reader_id] = 0
        return

    card_id = int(raw_value)

    # 3. Se o ID for 0 ou negativo, considera que não há cartão
    if card_id <= 0:
        last_seen[reader_id] = 0
        return

    # 4. Deteta NOVO cartão (diferente da última leitura imediata)
    if card_id != last_seen[reader_id]:
        print(f"New card detected on {reader_id}: {card_id}")
        last_seen[reader_id] = card_id
        
        # Inicia o pedido à API
        scan(reader_id, card_id)


def scan(reader_id: str, card_id: int):
    """
    Envia o pedido de validação do cartão para a API.
    """
    url = f"{URL}/api/rfids/scan"
    body = json.dumps({"token": TOKEN, "rfid_tag": card_id, "door_id": reader_id})

    http.onDone(on_scan_response)
    http.post(url, body)


def on_scan_response(status, data):
    """
    Processa a resposta da API.
    Define os comandos (abrir porta/acender luz) com base na autorização.
    """
    global pending_rfid_cmd, pending_door_cmd

    # Tratamento de erros de rede
    if status == 504:
        print("Received 504 Gateway Timeout from the server")
        return
    if status != 200:
        print(f"Scan request failed with status {status}")
        return

    try:
        json_data = json.loads(data)
    except Exception as e:
        print(f"Failed to parse JSON: {e}")
        return

    print("Scan response JSON:", json.dumps(json_data, indent=4))

    # Extrai dados da resposta
    authorized = json_data.get("authorized")
    message = json_data.get("message")
    door_id = json_data.get("door_id")
    rfid_tag = json_data.get("rfid_tag")

    # Define comando para o MCU de RFID (Luzes)
    # Ex: "VALID:office" ou "INVALID:office"
    rfid_action = "VALID" if authorized else "INVALID"
    pending_rfid_cmd = f"{rfid_action}:{door_id}"

    # Define comando para o MCU de Portas (Trinco)
    # Ex: "OPEN:office" ou "CLOSE:office"
    door_action = "OPEN" if authorized else "CLOSE"
    pending_door_cmd = f"{door_action}:{door_id}"

    # Regista o evento no histórico da API
    update_rfid_history(door_id, rfid_tag, message)


# LÓGICA DE COMUNICAÇÃO COM MCUS

def send_rfid_pending_command():
    """
    Envia comandos pendentes para o MCU que controla as luzes RFID.
    """
    global pending_rfid_cmd

    if pending_rfid_cmd is None:
        return

    # Escreve o comando no pino de saída
    customWrite(MCU_RFID_CMD_PIN, pending_rfid_cmd)
    print(f"Sent rfid command to MCU: {pending_rfid_cmd}")

    # Mantém o sinal por 1 segundo e depois limpa ("NONE")
    sleep(1)
    customWrite(MCU_RFID_CMD_PIN, "NONE")

    pending_rfid_cmd = None


def send_door_pending_command():
    """
    Envia comandos pendentes para o MCU que controla as portas.
    """
    global pending_door_cmd

    if pending_door_cmd is None:
        return

    # Escreve o comando no pino de saída
    customWrite(MCU_DOOR_CMD_PIN, pending_door_cmd)
    print(f"Sent door command to MCU: {pending_door_cmd}")

    # Mantém o sinal por 1 segundo e depois limpa ("NONE")
    sleep(1)
    customWrite(MCU_DOOR_CMD_PIN, "NONE")

    pending_door_cmd = None

# FUNÇÕES AUXILIARES

def update_rfid_history(door_id, card_id, message):
    """
    Envia log para a API.
    """
    url = f"{URL}/api/rfids/{door_id}/history"
    body = json.dumps({"token": TOKEN, "rfid_tag": card_id, "message": message})

    http.onDone(lambda status, data: None)
    http.post(url, body)

# SETUP E LOOP PRINCIPAL

def setup():
    """
    Configuração inicial dos pinos.
    """
    # CORREÇÃO: Pinos de comando devem ser OUTPUT (OUT).
    # O SBC escreve neles para mandar ordens aos outros MCUs.
    pinMode(MCU_RFID_CMD_PIN, OUT)
    pinMode(MCU_DOOR_CMD_PIN, OUT)

    # Pinos dos leitores RFID são INPUT (IN).
    # O SBC lê o ID do cartão a partir deles.
    for pin in RFID_SENSORS.values():
        pinMode(pin, IN)

def loop():
    """
    Loop principal: Verifica cartões e processa filas de comandos.
    """
    # 1. Verifica todos os leitores RFID
    for reader_id, pin in RFID_SENSORS.items():
        detect_card_hover(reader_id, pin)

    # 2. Envia comandos pendentes (se houver resposta da API)
    send_rfid_pending_command()
    send_door_pending_command()

# Ponto de entrada
if __name__ == "__main__":
    setup()
    while True:
        loop()