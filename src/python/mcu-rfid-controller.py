from gpio import *
from time import *

# Mapping: id -> pin (D1 pins for RFID lights)
RFID_SENSORS = {"main-entrance": 1, "Lab": 2, "Office": 3, "Lockers": 4}

# Command from SBC
SBC_CMD_PIN = 0

# RFID states
VALID = 0
INVALID = 1
WAITING = 2

# Core functions


def set_rfid_valid(pin):
    customWrite(pin, VALID)
    sleep(1)
    set_rfid_waiting(pin)


def set_rfid_invalid(pin):
    customWrite(pin, INVALID)
    sleep(1)
    set_rfid_waiting(pin)


def set_rfid_waiting(pin):
    customWrite(pin, WAITING)


# Other functions


def poll_rfid_commands():
    global last_cmd, last_cmd_time

    cmd = customRead(SBC_CMD_PIN)

    if cmd in ["NONE", "0", ""]:
        return

    if not (
        cmd.startswith("VALID:")
        or cmd.startswith("INVALID:")
        or cmd.startswith("WAITING:")
    ):
        print("Unknown command format received:", cmd)
        return

    reader_id = cmd.split(":")[1]

    if reader_id not in RFID_SENSORS.keys():
        print("Unknown reader ID in command:", reader_id)

    pin = RFID_SENSORS[reader_id]
    print(f"Processing command '{cmd}' for reader '{reader_id}' on pin {pin}")

    if cmd.startswith("VALID:"):
        set_rfid_valid(pin)
    elif cmd.startswith("INVALID:"):
        set_rfid_invalid(pin)
    elif cmd.startswith("WAITING:"):
        set_rfid_waiting(pin)


# Setup and loop


def setup():
    for pin in RFID_SENSORS.values():
        pinMode(pin, IN)
        set_rfid_waiting(pin)
    pinMode(SBC_CMD_PIN, IN)


def loop():
    poll_rfid_commands()


# Entry point

setup()
while True:
    loop()
