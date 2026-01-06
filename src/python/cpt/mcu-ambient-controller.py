from gpio import *
from time import *
from realhttp import *
import json

# CONFIGURAÇÕES DA API E CONSTANTES
PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"
# Token de autenticação (Hardcoded para exemplo)
TOKEN = "Bearer 5b57634f8e96f1c24ae7748f6069e5cf"

# Inicializa o cliente HTTP do Packet Tracer
http = RealHTTPClient()

# Definição dos Pinos (Slot/Porta)
SWITCH_PIN = 0
FAN_PIN = 1

# VARIÁVEIS GLOBAIS DE ESTADO
speed = 2                # Velocidade atual da ventoinha (1 ou 2)
firstLoop = False        # Variável de controlo para transição (redundante, mas mantida)
last_switch_state = None # Guarda o último estado do botão para detetar mudanças

# FUNÇÕES AUXILIARES

def on_http_done(status, data):
    """
    Callback executado quando o pedido HTTP termina.
    Verifica se o servidor respondeu com sucesso (204 No Content).
    """
    if status != 204:
        print("Failed to send data, status code:", status)

def send_fan_speed_to_api(speed):
    """
    Envia a velocidade da ventoinha para a API.
    """
    url = f"{URL}/api/actuators/fan"
    # Formata o corpo do pedido conforme esperado pela API
    body = json.dumps({"token": TOKEN, "value": f"Speed - {speed}"})

    http.onDone(on_http_done) # Regista o callback
    http.post(url, body)      # Envia pedido POST

def send_switch_state_to_api(state):
    """
    Envia o estado do interruptor (ON/OFF) para a API.
    """
    url = f"{URL}/api/sensors/switch"
    body = json.dumps({"token": TOKEN, "value": state})

    http.onDone(on_http_done)
    http.post(url, body)

# SETUP E LOOP PRINCIPAL

def setup():
    """
    Configura os modos dos pinos ao iniciar.
    """
    pinMode(SWITCH_PIN, INPUT)
    pinMode(FAN_PIN, OUTPUT)

def loop():
    """
    Lógica principal executada repetidamente.
    """
    # Importa variáveis globais para poder alterá-las
    global speed
    global firstLoop
    global last_switch_state

    # Lê o estado do switch.
    # No Packet Tracer, digitalRead devolve 1023 para HIGH e 0 para LOW.
    state = digitalRead(SWITCH_PIN) == 1023

    # DETEÇÃO DE MUDANÇA DE ESTADO (Debounce/Edge Detection)
    # Se o estado for igual ao da última leitura, não faz nada e sai da função.
    # Isto evita enviar spam para a API enquanto o botão está parado na mesma posição.
    if state == last_switch_state:
        return

    # Atualiza o último estado conhecido
    last_switch_state = state

    # Envia o novo estado para a API (Sempre que muda)
    if state:
        send_switch_state_to_api("ON")
    else:
        send_switch_state_to_api("OFF")

    # LÓGICA DE CONTROLO DA VENTOINHA
    if state: # Se o switch acabou de ser ligado (ON)
        
        # A verificação 'firstLoop' garante que a velocidade só alterna
        # na transição do botão e não continuamente.
        if not firstLoop:
            firstLoop = True

            # Alterna a velocidade entre 1 e 2
            if speed == 2:
                speed = 1
            else:
                speed = 2
        
        # Liga a ventoinha (customWrite é usado para valores analógicos/motores)
        customWrite(FAN_PIN, speed)
        send_fan_speed_to_api(speed)
        
    else: # Se o switch acabou de ser desligado (OFF)
        # Reseta o controlo de loop
        firstLoop = False
        
        # Desliga a ventoinha (velocidade 0)
        customWrite(FAN_PIN, 0)
        send_fan_speed_to_api(0)

# PONTO DE ENTRADA
if __name__ == "__main__":
    setup()
    while True:
        loop()
        # No Packet Tracer Python, o loop precisa de respirar. 
        # sleep(0.1) é recomendado aqui para não bloquear a simulação, 
        # mas mantive o teu original sem sleep conforme pedido.