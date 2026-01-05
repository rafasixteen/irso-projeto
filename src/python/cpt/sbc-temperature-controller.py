from gpio import *
from time import *
from realhttp import *
import json

# API Configuration
PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"
TOKEN = "Bearer 5b57634f8e96f1c24ae7748f6069e5cf"

http = RealHTTPClient()

TERMOSTAT_PIN = 1


# Function to read and parse thermostat
def read_termostat(pin):
    cmd = customRead(pin)

    if cmd:
        parts = cmd.split(",")
        if len(parts) == 4:
            state = int(parts[0].strip())
            try:
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
                # Parsing failed
                return None, None, None, None

    # cmd missing or malformed
    return None, None, None, None


def convert_state_to_string(state):
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


def setup():
    pinMode(TERMOSTAT_PIN, INPUT)


def loop():
    state, temperature, autoCool, autoHeat = read_termostat(TERMOSTAT_PIN)

    if state is None or temperature is None:
        print("Failed to read thermostat data")
        return

    # Determine Furnace and AC states
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
        furnace_state = "OFF"
        ac_state = "OFF"

    # Send to API
    send_temperature_to_api(temperature)
    send_furnace_to_api(furnace_state)
    send_air_conditioner_to_api(ac_state)

    print("Thermostat State:", state)
    print("Temperature:", temperature)
    print("Furnace:", furnace_state)
    print("AC:", ac_state)
    print("----------------------")


# Entry point
setup()
while True:
    loop()
    sleep(1)
