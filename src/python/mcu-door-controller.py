from gpio import *
from time import *

# Door inputs
DOOR_PINS = [1, 2, 3, 4]

# Command from SBC
SBC_CMD_PIN = 0

# Door states
CLOSE = 0
OPEN = 1
UNLOCK = 0
LOCK = 1

# Core functions

def set_door_state(pin, door_state, lock_state):
    # 1. Validate values
    if door_state not in (OPEN, CLOSE):
        raise ValueError("Invalid door_state")
	
    if lock_state not in (LOCK, UNLOCK):
        raise ValueError("Invalid lock_state")

    # 2. Read current state
    current_door_state, current_lock_state = get_door_state(pin)

    # 3. No-op optimization
    if door_state == current_door_state and lock_state == current_lock_state:
        return

    # 4. Apply change
    cmd = f"{door_state},{lock_state}"
    customWrite(pin, cmd)

    # 5. Update software state
    update_door_state(pin, door_state, lock_state)

def get_door_state(pin):
	state = customRead(pin)
	door_state, lock_state = map(int, state.split(","))
	return door_state, lock_state

# Convenience functions

def open_door(pin):
    _, lock = get_door_state(pin)
    set_door_state(pin, OPEN, lock)

def close_door(pin):
    _, lock = get_door_state(pin)
    set_door_state(pin, CLOSE, lock)

def unlock_door(pin):
    door, _ = get_door_state(pin)
    set_door_state(pin, door, UNLOCK)

def lock_door(pin):
    door, _ = get_door_state(pin)
    set_door_state(pin, door, LOCK)

def is_door_open(pin):
    door, _ = get_door_state(pin)
    return door == OPEN

def is_door_closed(pin):
    door, _ = get_door_state(pin)
    return door == CLOSE

def is_door_locked(pin):
    _, lock = get_door_state(pin)
    return lock == LOCK

def is_door_unlocked(pin):
    _, lock = get_door_state(pin)
    return lock == UNLOCK

def update_door_state(pin, door_state, lock_state):
	# Send state to SBC to update the API
	pass

# Setup and loop

def setup():
    for p in DOOR_PINS:
        pinMode(p, OUT)
        close_door(p)
        lock_door(p)
    pinMode(SBC_CMD_PIN, IN)

def loop():
	cmd = customRead(SBC_CMD_PIN)

	for pin in DOOR_PINS:
		if cmd == f"OPEN:{pin}":
			open_door(pin)
		elif cmd == f"CLOSE:{pin}":
			close_door(pin)
		elif cmd == f"UNLOCK:{pin}":
			unlock_door(pin)
		elif cmd == f"LOCK:{pin}":
			lock_door(pin)

# Entry point

setup()

while True:
	loop()
	sleep(0.1)