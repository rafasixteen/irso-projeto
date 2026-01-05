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
MCU_DOOR_CMD_PIN = 0

# Track last processed time per card
last_scan_time = {}

# Mapping: id -> pin (D1 pins for RFID lights)
RFID_SENSORS = {"main-entrance": 2, "Lab": 3, "Office": 4, "Lockers": 5}

# Track last seen card per reader ID
last_seen = {reader_id: 0 for reader_id in RFID_SENSORS.keys()}

# Pending commands to send to MCU's
pending_rfid_cmd = None
pending_door_cmd = None

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
        scan(reader_id, str(card_id))


def on_scan_response(status, data):
    global pending_rfid_cmd, pending_door_cmd

    try:
        json_data = json.loads(data)
    except Exception as e:
        print(f"Scan request failed with status {status}")
        print(e)
        return

    # print(json.dumps(json_data, indent=4))

    authorized = json_data.get("authorized")
    message = json_data.get("message")
    door_id = json_data.get("door_id")
    rfid_tag = json_data.get("rfid_tag")

    rfid_cmd = "VALID" if authorized else "INVALID"
    pending_rfid_cmd = f"{rfid_cmd}:{door_id}"

    door_cmd = "OPEN" if authorized else "CLOSE"
    pending_door_cmd = f"{door_cmd}:{door_id}"

    update_rfid_history(door_id, rfid_tag, message)


def scan(reader_id: str, card_id: str):
    url = f"{URL}/api/rfids/scan"
    body = json.dumps({"rfid_tag": card_id, "door_id": reader_id})

    http.onDone(on_scan_response)
    http.post(url, body)


# History update functions


def update_rfid_history(door_id, card_id, message):
    url = f"{URL}/api/rfids/{door_id}/history"
    body = json.dumps({"rfid_tag": card_id, "message": message})

    http.onDone(lambda status, data: None)
    http.post(url, body)


# Pending command functions


def send_rfid_pending_command():
    global pending_rfid_cmd

    if pending_rfid_cmd is None:
        return

    customWrite(MCU_RFID_CMD_PIN, pending_rfid_cmd)
    print(f"Sent rfid command to MCU: {pending_rfid_cmd}")

    sleep(1)
    customWrite(MCU_RFID_CMD_PIN, "NONE")

    pending_rfid_cmd = None


def send_door_pending_command():
    global pending_door_cmd

    if pending_door_cmd is None:
        return

    customWrite(MCU_DOOR_CMD_PIN, pending_door_cmd)
    print(f"Sent door command to MCU: {pending_door_cmd}")

    sleep(1)
    customWrite(MCU_DOOR_CMD_PIN, "NONE")

    pending_door_cmd = None


# Setup and loop


def setup():
    pinMode(MCU_RFID_CMD_PIN, IN)
    pinMode(MCU_DOOR_CMD_PIN, IN)

    for pin in RFID_SENSORS.values():
        pinMode(pin, IN)


def loop():
    for reader_id, pin in RFID_SENSORS.items():
        detect_card_hover(reader_id, pin)

    send_rfid_pending_command()
    send_door_pending_command()


# Entry point

setup()
while True:
    loop()
