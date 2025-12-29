from gpio import *
from realhttp import *
from time import *

# ================== CONFIG ==================

# Access-control server
SERVER_IP = "192.168.1.89"
SERVER_PORT = "8080"
ACCESS_BASE_URL = "http://" + SERVER_IP + ":" + SERVER_PORT

# Home Gateway IP (IoT API lives here)
GATEWAY_IP = "192.168.25.1"

http = RealHTTPClient()

# SBC → LED / external controller
PIN_TO_SBC = 8

# INPUT_PIN : [door_name_for_server, SmartDoor_DeviceID, sbc_light_id]
doors = {
    0: ["Main_Entrance", "EntradaAutomatica", 1],
    2: ["Back_Door", "SmartDoor1", 2],
    4: ["Warehouse_Gate", "SmartDoor2", 3],
    6: ["Server_Room_Secure", "SmartDoor3", 4]
}

# ================== HTTP SYNC SUPPORT ==================

last_status = None
last_body = None
request_done = False

def onHTTPDone(status, data):
    global last_status, last_body, request_done
    last_status = status
    last_body = str(data).strip().upper()
    request_done = True

def http_get_sync(url, timeout=5):
    global request_done, last_status, last_body
    request_done = False
    last_status = None
    last_body = None

    http.get(url)
    http.onDone(onHTTPDone)

    start = time()
    while not request_done and (time() - start) < timeout:
        sleep(0.05)

    if not request_done:
        raise Exception("HTTP TIMEOUT")

    return last_status, last_body

# ================== SMART DOOR CONTROL (CORRECT API) ==================

def set_door_lock(door_device_id, locked):
    """
    Controls SmartDoor via IoT HTTP API.
    locked=True  -> LOCKED
    locked=False -> UNLOCKED
    """
    state = "LOCKED" if locked else "UNLOCKED"

    # CORRECT Packet Tracer IoT API format
    url = f"http://{GATEWAY_IP}/api/actuators/{door_device_id}/lockState/{state}"

    print(" >> DOOR COMMAND:", url)

    try:
        status, body = http_get_sync(url)
        print(" >> DOOR API:", status, "|", body)
    except Exception as e:
        print(" >> DOOR API ERROR:", str(e))

# ================== SBC LIGHT COMMAND ==================

def send_light_command(light_id, color_code):
    cmd = str(light_id) + ":" + str(color_code)
    customWrite(PIN_TO_SBC, cmd)
    sleep(0.2)
    customWrite(PIN_TO_SBC, "0")

# ================== SETUP ==================

def setup():
    print("--- SBC ONLINE (PORT " + SERVER_PORT + ") ---")
    pinMode(PIN_TO_SBC, OUT)

    # Card reader pins
    for pin in doors.keys():
        pinMode(pin, IN)

# ================== MAIN LOOP ==================

def main():
    setup()

    while True:
        for pin_num, details in doors.items():
            door_name = details[0]
            door_device_id = details[1]
            sbc_light_id = details[2]

            raw_val = customRead(pin_num)
            card_id = str(raw_val).strip()

            if card_id == "" or card_id == "0" or card_id == "NONE":
                continue

            print("\n[CHECKING] " + door_name + " | CARD:", card_id)

            try:
                url = (
                    ACCESS_BASE_URL
                    + "?card_id=" + card_id
                    + "&door_id=" + door_name
                    + "&nocache=" + str(time())
                )

                status, body = http_get_sync(url)
                print("HTTP:", status, "| RESPONSE:", body)

                # ---------- ERROR ----------
                if status != 200:
                    print(" >> HTTP ERROR")
                    set_door_lock(door_device_id, True)
                    send_light_command(sbc_light_id, 1)
                    continue

                # ---------- ACCESS GRANTED ----------
                if body == "TRUE":
                    print(" >> ACCESS GRANTED")
                    set_door_lock(door_device_id, False)   # UNLOCK
                    send_light_command(sbc_light_id, 0)
                    sleep(3)
                    set_door_lock(door_device_id, True)    # LOCK
                    send_light_command(sbc_light_id, 2)

                # ---------- ACCESS DENIED ----------
                elif body == "FALSE":
                    print(" >> ACCESS DENIED")
                    set_door_lock(door_device_id, True)
                    send_light_command(sbc_light_id, 1)
                    sleep(2)
                    send_light_command(sbc_light_id, 2)

                # ---------- INVALID RESPONSE ----------
                else:
                    print(" >> INVALID RESPONSE")
                    set_door_lock(door_device_id, True)
                    send_light_command(sbc_light_id, 1)
                    sleep(1)
                    send_light_command(sbc_light_id, 2)

            except Exception as e:
                print("NETWORK ERROR:", str(e))
                set_door_lock(door_device_id, True)
                send_light_command(sbc_light_id, 1)
                sleep(1)
                send_light_command(sbc_light_id, 2)

            sleep(0.5)

        sleep(0.1)

# ================== ENTRY POINT ==================

if __name__ == "__main__":
    main()
