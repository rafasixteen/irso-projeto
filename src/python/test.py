import urllib.request
import json
from realhttp import *

PROTOCOL = "http"
DOMAIN = "localhost:8000"
API = "api/users"

URL = f"{PROTOCOL}://{DOMAIN}/{API}"

MD5_TOKEN = "5b57634f8e96f1c24ae7748f6069e5cf"
bearer_token = f"Bearer {MD5_TOKEN}"

request = urllib.request.Request(
    URL,
    headers={
        "Content-Type": "application/json",
        "Authorization": bearer_token
    },
    method="GET"
)

try:
    response = urllib.request.urlopen(request)
    raw_body = response.read().decode("utf-8")

    try:
        parsed_json = json.loads(raw_body)
        print(json.dumps(parsed_json, indent=2))
    except json.JSONDecodeError:
        print(json.dumps({
            "status": response.status,
            "raw_response": raw_body
        }, indent=2))

except Exception as e:
    print(json.dumps({
        "error": str(e)
    }, indent=2))