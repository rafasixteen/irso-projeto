import json
from gpio import *
from time import *
from realhttp import *

# API Configuration

PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"
TOKEN = "Bearer 5b57634f8e96f1c24ae7748f6069e5cf"

http = RealHTTPClient()

# Mapping: id -> pin
DOOR_PINS = {
    "main-entrance": 2,
    "office": 1,
    "locker-room-male": 3,
    "locker-room-female": 4,
}

# Command from SBC
SBC_CMD_PIN = 0

# Door states
CLOSE = 0
OPEN = 1
UNLOCK = 0
LOCK = 1

# Core functions


def set_door_state(door_name, door_state, lock_state):
    # 1. Validate values
    if door_state not in (OPEN, CLOSE):
        raise ValueError("Invalid door_state")

    if lock_state not in (LOCK, UNLOCK):
        raise ValueError("Invalid lock_state")

    # 2. Read current state
    current_door_state, current_lock_state = get_door_state(door_name)

    # 3. No-op optimization
    if door_state == current_door_state and lock_state == current_lock_state:
        return

    # 4. Apply change
    cmd = f"{door_state},{lock_state}"
    customWrite(DOOR_PINS[door_name], cmd)


def get_door_state(door_name):
    pin = DOOR_PINS[door_name]
    state = customRead(pin)
    door_state, lock_state = map(int, state.split(","))
    return door_state, lock_state


# Convenience functions


def update_door_history(door_id, action):
    url = f"{URL}/api/doors/{door_id}/history"
    body = json.dumps({"token": TOKEN, "state": action})

    http.onDone(lambda status, data: None)
    http.post(url, body)


def open_door(door_name):

    unlock_door(door_name)
    sleep(0.5)

    print("Opening door", door_name)

    _, lock = get_door_state(door_name)
    set_door_state(door_name, OPEN, lock)

    update_door_history(door_name, "OPEN")

    sleep(5)
    close_door(door_name)


def close_door(door_name):
    print("Closing door", door_name)

    _, lock = get_door_state(door_name)
    set_door_state(door_name, CLOSE, lock)

    update_door_history(door_name, "CLOSED")

    sleep(1)
    lock_door(door_name)


def unlock_door(door_name):
    print("Unlocking door", door_name)

    door, _ = get_door_state(door_name)
    set_door_state(door_name, door, UNLOCK)

    update_door_history(door_name, "UNLOCKED")


def lock_door(door_name):
    print("Locking door", door_name)

    door, _ = get_door_state(door_name)
    set_door_state(door_name, door, LOCK)

    update_door_history(door_name, "LOCKED")


# Setup and loop


def setup():
    for door_name in DOOR_PINS.keys():
        pinMode(DOOR_PINS[door_name], OUT)
        close_door(door_name)

    pinMode(SBC_CMD_PIN, IN)


def loop():
    cmd = customRead(SBC_CMD_PIN)

    if cmd in ["NONE", "0", ""]:
        return

    if not (
        cmd.startswith("OPEN:")
        or cmd.startswith("CLOSE:")
        or cmd.startswith("UNLOCK:")
        or cmd.startswith("LOCK:")
    ):
        print("Unknown command format received:", cmd)
        return

    door_name = cmd.split(":")[1]

    if cmd.startswith("OPEN:"):
        open_door(door_name)
    elif cmd.startswith("CLOSE:"):
        close_door(door_name)
    elif cmd.startswith("UNLOCK:"):
        unlock_door(door_name)
    elif cmd.startswith("LOCK:"):
        lock_door(door_name)


# Entry point

setup()
while True:
    loop()
