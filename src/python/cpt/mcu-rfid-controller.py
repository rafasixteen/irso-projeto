from gpio import *
from time import *

# MAPEAMENTO DE HARDWARE
# Mapeia o ID do leitor (string) para o pino físico (D1 para as luzes)
RFID_SENSORS = {
    "main-entrance": 1,
    "office": 2,
    "locker-room-male": 4,
    "locker-room-female": 3,
}

# Pino de entrada onde o SBC envia comandos
SBC_CMD_PIN = 0

# CONSTANTES DE ESTADO (VISUAL)
VALID = 0   # Verde
INVALID = 1 # Vermelho
WAITING = 2 # Preto/Desligado

# FUNÇÕES CORE (CONTROLO VISUAL)

def set_rfid_waiting(pin):
    """
    Define o estado visual do leitor para 'Aguardar' (Reset).
    """
    customWrite(pin, WAITING)

def set_rfid_valid(pin):
    """
    Pisca o estado 'Válido' (Verde) por 1 segundo e reseta.
    """
    customWrite(pin, VALID)
    sleep(1)
    set_rfid_waiting(pin)

def set_rfid_invalid(pin):
    """
    Pisca o estado 'Inválido' (Vermelho) por 1 segundo e reseta.
    """
    customWrite(pin, INVALID)
    sleep(1)
    set_rfid_waiting(pin)

# FUNÇÕES DE LÓGICA

def poll_rfid_commands():
    """
    Lê o pino do SBC, processa o comando e aciona o leitor correto.
    """
    # Lê o comando do SBC
    cmd = customRead(SBC_CMD_PIN)

    # 1. Proteção contra leituras vazias ou tipos incorretos (Int/None)
    # Se recebermos um 0 ou None, o código 'crashava' nas linhas seguintes.
    if not cmd or not isinstance(cmd, str) or cmd in ["NONE", "0", ""]:
        return

    # 2. Validação de formato (Espera-se "ACAO:ID_DO_LEITOR")
    if ":" not in cmd:
        print("Unknown command format received:", cmd)
        return

    try:
        # Separa a ação e o ID
        parts = cmd.split(":")
        action = parts[0]
        reader_id = parts[1]

        # Verifica se o leitor existe no nosso dicionário
        if reader_id not in RFID_SENSORS:
            print(f"Unknown reader ID in command: {reader_id}")
            return
        
        # Obtém o pino correspondente
        pin = RFID_SENSORS[reader_id]
        print(f"Processing command '{cmd}' for reader '{reader_id}' on pin {pin}")

        # Executa a ação
        if action == "VALID":
            set_rfid_valid(pin)
        elif action == "INVALID":
            set_rfid_invalid(pin)
        elif action == "WAITING":
            set_rfid_waiting(pin)
        else:
            print(f"Unknown action: {action}")

    except Exception as e:
        print(f"Error parsing command '{cmd}': {e}")

# SETUP E LOOP PRINCIPAL

def setup():
    """
    Configura os pinos.
    """
    # Configura os leitores RFID
    for pin in RFID_SENSORS.values():
        # CORREÇÃO: Mudei de IN para OUT.
        # Como usamos 'customWrite' para mudar as luzes do leitor,
        # o pino tem de ser uma SAÍDA do MCU.
        pinMode(pin, OUT)
        set_rfid_waiting(pin)

    # Configura o pino de comunicação com o SBC
    # Mantém-se IN pois lemos dados dele.
    pinMode(SBC_CMD_PIN, IN)

def loop():
    poll_rfid_commands()

# Ponto de entrada
if __name__ == "__main__":
    setup()
    while True:
        loop()