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

TERMOSTAT_PIN = 1

# FUNÇÕES DE LEITURA E PARSING

def convert_state_to_string(state):
    """
    Converte o estado numérico do termostato para texto.
    """
    if state == 0:
        return "OFF"
    elif state == 1:
        return "COOLING"
    elif state == 2:
        return "HEATING"
    elif state == 3:
        return "AUTO"
    else:
        return "UNKNOWN"


def read_termostat(pin):
    """
    Lê o valor do pino e tenta fazer o parse da string "State,Temp,Cool,Heat".
    """
    cmd = customRead(pin)

    # Verifica se cmd não é vazio ou None
    if cmd:
        # Tenta partir a string
        parts = cmd.split(",")
        if len(parts) == 4:
            try:
                state = int(parts[0].strip())
                temperature = float(parts[1])
                autoCool = float(parts[2])
                autoHeat = float(parts[3])
                
                return (
                    convert_state_to_string(state),
                    round(temperature, 1),
                    round(autoCool, 1),
                    round(autoHeat, 1),
                )
            except:
                # Se falhar a conversão para int/float
                return None, None, None, None

    # Se o cmd for inválido ou malformado
    return None, None, None, None


# FUNÇÕES DE API

def on_http_done(status, data):
    if status != 204:
        print("Failed to send data, status code:", status)


def send_temperature_to_api(temperature):
    url = f"{URL}/api/sensors/termostat"
    body = json.dumps({"token": TOKEN, "value": f"{temperature} ºC"})

    http.onDone(on_http_done)
    http.post(url, body)


def send_furnace_to_api(state):
    url = f"{URL}/api/actuators/furnace"
    body = json.dumps({"token": TOKEN, "value": state})

    http.onDone(on_http_done)
    http.post(url, body)


def send_air_conditioner_to_api(state):
    url = f"{URL}/api/actuators/air-conditioner"
    body = json.dumps({"token": TOKEN, "value": state})

    http.onDone(on_http_done)
    http.post(url, body)


# SETUP E LOOP

def setup():
    # CORREÇÃO: Mudei de INPUT para IN.
    # Como estamos a ler dados de um sensor (customRead), o modo correto é IN.
    pinMode(TERMOSTAT_PIN, IN)


def loop():
    # 1. Leitura
    state, temperature, autoCool, autoHeat = read_termostat(TERMOSTAT_PIN)

    # Se a leitura falhar, sai e tenta na próxima iteração
    if state is None or temperature is None:
        print("Failed to read thermostat data")
        return

    # 2. Lógica de decisão (Mantida exatamente igual ao original)
    if state == "OFF":
        furnace_state = "OFF"
        ac_state = "OFF"
    elif state == "COOLING":
        furnace_state = "OFF"
        ac_state = "ON"
    elif state == "HEATING":
        furnace_state = "ON"
        ac_state = "OFF"
    elif state == "AUTO":
        if temperature > autoCool:
            furnace_state = "OFF"
            ac_state = "ON"
        elif temperature < autoHeat:
            furnace_state = "ON"
            ac_state = "OFF"
        else:
            furnace_state = "OFF"
            ac_state = "OFF"
    else:
        # Estado desconhecido
        furnace_state = "OFF"
        ac_state = "OFF"

    # 3. Envio para API (Envia sempre, a cada ciclo)
    send_temperature_to_api(temperature)
    send_furnace_to_api(furnace_state)
    send_air_conditioner_to_api(ac_state)

    print("Thermostat State:", state)
    print("Temperature:", temperature)
    print("Furnace:", furnace_state)
    print("AC:", ac_state)
    print("----------------------")


# Ponto de entrada
setup()
while True:
    loop()
    sleep(1)