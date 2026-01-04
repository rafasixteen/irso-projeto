from gpio import *
from time import *
from realhttp import *
import json

# API Configuration

PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"

http = RealHTTPClient()

# Cmd pin
MCU_RFID_CMD_PIN = 1

# Track last processed time per card
last_scan_time = {}

# Mapping: id -> pin (D1 pins for RFID lights)
RFID_SENSORS = {"Entrance": 2, "Lab": 3, "Office": 4, "Lockers": 5}

# Track last seen card per reader ID
last_seen = {reader_id: 0 for reader_id in RFID_SENSORS.keys()}

# Scan functions


def detect_card_hover(reader_id, pin):
    card_id = int(customRead(pin))

    # No card present
    if card_id <= 0:
        last_seen[reader_id] = 0
        return

    # New card detected
    if card_id != last_seen[reader_id]:
        last_seen[reader_id] = card_id

        # Scan the card
        scan(reader_id, card_id)


def on_scan_response(status, data):
    try:
        json_data = json.loads(data)
    except Exception as e:
        print(f"Scan request failed with status {status}")
        print(e)
        return

    authorized = json_data.get("authorized")
    door_id = json_data.get("door_id")

    keyword = "VALID" if authorized else "INVALID"
    cmd = f"{keyword}:{door_id}"

    customWrite(MCU_RFID_CMD_PIN, cmd)
    print(f"Sending command '{cmd}' to MCU")


def scan(reader_id: str, card_id: int):
    body = json.dumps({"rfid_tag": card_id, "door_id": reader_id})

    url = f"{URL}/api/rfids/scan"
    http.onDone(on_scan_response)
    http.post(url, body)


# Setup and loop


def setup():
    for pin in RFID_SENSORS.values():
        pinMode(pin, IN)
    pinMode(MCU_RFID_CMD_PIN, IN)


def loop():
    for reader_id, pin in RFID_SENSORS.items():
        detect_card_hover(reader_id, pin)


# Entry point

setup()
while True:
    loop()
