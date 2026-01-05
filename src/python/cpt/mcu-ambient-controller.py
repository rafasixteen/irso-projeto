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

SWITCH_PIN = 0
FAN_PIN = 1

speed = 2
firstLoop = False
last_switch_state = None


def on_http_done(status, data):
    if status != 204:
        print("Failed to send data, status code:", status)


def send_fan_speed_to_api(speed):
    url = f"{URL}/api/actuators/fan"
    body = json.dumps({"token": TOKEN, "value": f"Speed - {speed}"})

    http.onDone(on_http_done)
    http.post(url, body)


def send_switch_state_to_api(state):
    url = f"{URL}/api/sensors/switch"
    body = json.dumps({"token": TOKEN, "value": state})

    http.onDone(on_http_done)
    http.post(url, body)


def setup():
    pinMode(SWITCH_PIN, INPUT)
    pinMode(FAN_PIN, OUTPUT)


def loop():
    global speed
    global firstLoop
    global last_switch_state

    # Read switch state
    state = digitalRead(SWITCH_PIN) == 1023

    if state == last_switch_state:
        return

    last_switch_state = state

    if state:
        send_switch_state_to_api("ON")
    else:
        send_switch_state_to_api("OFF")

    # Detect OFF -> ON transition
    if state:
        if not firstLoop:
            firstLoop = True

            # Toggle speed
            if speed == 2:
                speed = 1
            else:
                speed = 2
    else:
        # Reset when switch turns OFF
        firstLoop = False

    # Control fan
    if state:
        customWrite(FAN_PIN, speed)
        send_fan_speed_to_api(speed)
    else:
        customWrite(FAN_PIN, 0)
        send_fan_speed_to_api(0)


# Entry point
setup()
while True:
    loop()
