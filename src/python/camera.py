import cv2
import requests
import time
import sys
import json

# -------------------------
# CONFIG
# -------------------------
PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"
TOKEN = "Bearer 5b57634f8e96f1c24ae7748f6069e5cf"
API_URL = f"{URL}/api/camera"

FRAME_WIDTH = 1280
FRAME_HEIGHT = 720
INTERVAL = 2  # seconds between captures
PING_INTERVAL = 2  # seconds between server pings
MAX_RETRIES = 10  # maximum number of server ping retries at startup


# -------------------------
# FUNCTIONS
# -------------------------
def is_server_alive():
    """Ping the server and return True if it responds."""
    try:
        response = requests.get(URL, timeout=3)
        return response.status_code < 500
    except requests.RequestException:
        return False


def find_camera(max_index=4):
    """Automatically detect the first available camera."""
    for i in range(max_index):
        cap = cv2.VideoCapture(i, cv2.CAP_DSHOW)
        if cap.isOpened():
            print(f"Camera detected at index {i}")
            return i, cap
        cap.release()
    return None, None


def capture_frame(cap):
    """Capture a single frame from the camera with warm-up."""
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)

    ret, frame = cap.read()
    if not ret:
        print("Failed to capture image from webcam.")
        return None
    return frame


def encode_image(frame):
    """Encode frame to JPEG in memory."""
    ret, buffer = cv2.imencode(".jpg", frame)
    if not ret:
        print("Failed to encode image.")
        return None
    return buffer.tobytes()


def send_to_api(image_bytes):
    """Send JPEG bytes to API and pretty-print the response."""
    files = {"image": ("camera_snapshot.jpg", image_bytes, "image/jpeg")}
    headers = {"Authorization": TOKEN}
    try:
        response = requests.post(API_URL, files=files, headers=headers, timeout=10)
        print("Status code:", response.status_code)
        try:
            data = response.json()
            print("Response:")
            print(json.dumps(data, indent=4))
        except json.JSONDecodeError:
            print("Response (raw):")
            print(response.text)
    except requests.RequestException as e:
        print("Failed to send image:", e)


# -------------------------
# MAIN LOOP
# -------------------------
def main():
    print(f"Waiting for server at {URL}...")

    retries = 0
    while not is_server_alive():
        retries += 1
        print(f"Server not responding. Retry {retries}/{MAX_RETRIES}...")
        if retries >= MAX_RETRIES:
            sys.exit("Max retries reached. Server is offline. Exiting.")
        time.sleep(PING_INTERVAL)

    print("Server is online. Starting camera uploader.")

    camera_index, cap = find_camera()
    if camera_index is None:
        sys.exit("No camera detected. Exiting.")

    while True:
        if not is_server_alive():
            print("Server went offline. Stopping camera uploader.")
            break

        frame = capture_frame(cap)
        if frame is not None:
            image_bytes = encode_image(frame)
            if image_bytes is not None:
                send_to_api(image_bytes)

        time.sleep(INTERVAL)

    cap.release()


if __name__ == "__main__":
    main()
