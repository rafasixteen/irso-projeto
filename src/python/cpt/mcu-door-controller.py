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

# MAPEAMENTO DE HARDWARE
# Mapeia o ID da porta (string) para o pino físico
DOOR_PINS = {
    "main-entrance": 2,
    "office": 1,
    "locker-room-male": 3,
    "locker-room-female": 4,
}

# Pino de entrada onde o SBC envia comandos
SBC_CMD_PIN = 0

# CONSTANTES DE ESTADO
CLOSE = 0
OPEN = 1
UNLOCK = 0
LOCK = 1

# FUNÇÕES CORE

def get_door_state(door_name):
    """
    Lê o estado atual da porta a partir do pino.
    """
    pin = DOOR_PINS[door_name]
    state = customRead(pin)

    # Proteção: Garante que 'state' é válido antes de processar
    if not state or not isinstance(state, str) or "," not in state:
        return CLOSE, LOCK

    parts = state.split(",")

    if len(parts) != 2 or parts[0] == "" or parts[1] == "":
        return CLOSE, LOCK

    return int(parts[0]), int(parts[1])


def set_door_state(door_name, door_state, lock_state):
    """
    Escreve o estado na porta (formato: "porta,fechadura").
    """
    if door_state not in (OPEN, CLOSE):
        raise ValueError("Invalid door_state")
    if lock_state not in (LOCK, UNLOCK):
        raise ValueError("Invalid lock_state")

    # Otimização: Só escreve se o estado for diferente
    current_door_state, current_lock_state = get_door_state(door_name)

    if door_state == current_door_state and lock_state == current_lock_state:
        return

    cmd = f"{door_state},{lock_state}"
    customWrite(DOOR_PINS[door_name], cmd)

# FUNÇÕES DE LÓGICA

def update_door_history(door_id, action):
    url = f"{URL}/api/doors/{door_id}/history"
    body = json.dumps({"token": TOKEN, "state": action})
    
    # Callback vazio
    http.onDone(lambda status, data: None)
    http.post(url, body)


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


def close_door(door_name):
    print("Closing door", door_name)
    _, lock = get_door_state(door_name)
    set_door_state(door_name, CLOSE, lock)
    update_door_history(door_name, "CLOSED")
    
    sleep(1)
    lock_door(door_name)


def open_door(door_name):
    unlock_door(door_name)
    sleep(0.5)

    print("Opening door", door_name)
    _, lock = get_door_state(door_name)
    set_door_state(door_name, OPEN, lock)
    update_door_history(door_name, "OPEN")

    sleep(5)
    close_door(door_name)

# SETUP E LOOP

def setup():
    for door_name in DOOR_PINS.keys():
        # As portas são OUTPUT (OUT) pois nós enviamos comandos para elas
        pinMode(DOOR_PINS[door_name], OUT)
        close_door(door_name)

    # O pino do SBC é INPUT (IN) pois nós lemos comandos dele
    pinMode(SBC_CMD_PIN, IN)


def loop():
    cmd = customRead(SBC_CMD_PIN)

    # Proteção contra leituras vazias ou inválidas
    if not cmd or not isinstance(cmd, str) or cmd in ["NONE", "0", ""]:
        return

    # Validação simples do formato
    if ":" not in cmd:
        print("Unknown command format:", cmd)
        return

    try:
        # Separa a ação do nome da porta (Ex: "OPEN:office")
        parts = cmd.split(":")
        action = parts[0]
        door_name = parts[1]

        if door_name not in DOOR_PINS:
            return

        if action == "OPEN":
            open_door(door_name)
        elif action == "CLOSE":
            close_door(door_name)
        elif action == "UNLOCK":
            unlock_door(door_name)
        elif action == "LOCK":
            lock_door(door_name)
            
    except Exception as e:
        print("Error parsing command:", e)

# Entry point
if __name__ == "__main__":
    setup()
    while True:
        loop()