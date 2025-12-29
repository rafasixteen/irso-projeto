from realhttp import *
from time import sleep

PROTOCOL = "http"
DOMAIN = "localhost:8000"
API = "api/users"

URL = f"{PROTOCOL}://{DOMAIN}/{API}"

MD5_TOKEN = "5b57634f8e96f1c24ae7748f6069e5cf"
bearer_token = f"Bearer {MD5_TOKEN}"

http = RealHTTPClient()

# Callback to handle async response
def on_done(status, data):
    print("Status:", status)
    print("Data:", data)

# Perform a GET request
http.get(URL)
http.onDone(on_done)

# Wait for response (synchronous loop)
import time
start = time.time()
while True:
    sleep(0.05)
    if time.time() - start > 5:
        print("Timeout")
        break
