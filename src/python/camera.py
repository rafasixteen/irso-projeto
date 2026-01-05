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

# Desired capture resolution
FRAME_WIDTH = 1280
FRAME_HEIGHT = 720


# -------------------------
# FUNCTIONS
# -------------------------
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
    # Set resolution
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)

    # Warm up
    time.sleep(2)

    ret, frame = cap.read()
    cap.release()
    if not ret:
        sys.exit("Failed to capture image from webcam. Exiting.")
    return frame


def encode_image(frame):
    """Encode frame to JPEG in memory."""
    ret, buffer = cv2.imencode(".jpg", frame)
    if not ret:
        sys.exit("Failed to encode image. Exiting.")
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
# MAIN
# -------------------------
def main():
    print(f"Using API URL: {API_URL}")

    camera_index, cap = find_camera()
    if camera_index is None:
        sys.exit("No camera detected on any index. Exiting.")

    frame = capture_frame(cap)
    image_bytes = encode_image(frame)
    send_to_api(image_bytes)


if __name__ == "__main__":
    main()
