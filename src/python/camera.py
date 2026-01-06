import cv2
import requests
import time
import sys
import json

# CONFIGURAÇÕES

# Configurações da API
PROTOCOL = "http"
DOMAIN = "localhost:8000"
URL = f"{PROTOCOL}://{DOMAIN}"
API_URL = f"{URL}/api/camera"
TOKEN = "Bearer 5b57634f8e96f1c24ae7748f6069e5cf"

# Configurações da Câmara
FRAME_WIDTH = 1280
FRAME_HEIGHT = 720

# Configurações de Loop e Rede
INTERVAL = 2       # Segundos entre capturas
PING_INTERVAL = 2  # Segundos entre tentativas de conexão ao servidor
MAX_RETRIES = 10   # Número máximo de tentativas ao iniciar

# FUNÇÕES DE REDE E UTILITÁRIOS

def is_server_alive():
    """
    Tenta contactar a raiz do servidor para verificar se está online.
    Retorna True se o status code for inferior a 500.
    """
    try:
        response = requests.get(URL, timeout=3)
        return response.status_code < 500
    except requests.RequestException:
        return False


def send_to_api(image_bytes):
    """
    Envia os bytes da imagem JPEG para a API via POST.
    Imprime a resposta formatada JSON na consola.
    """
    # Prepara o ficheiro para upload multipart/form-data
    files = {"image": ("camera_snapshot.jpg", image_bytes, "image/jpeg")}
    headers = {"Authorization": TOKEN}

    try:
        response = requests.post(API_URL, files=files, headers=headers, timeout=10)
        print(f"Status code: {response.status_code}")

        # Tenta formatar a resposta JSON para leitura fácil
        try:
            data = response.json()
            print("Response:")
            print(json.dumps(data, indent=4))
        except json.JSONDecodeError:
            # Fallback se a resposta não for JSON
            print("Response (raw):")
            print(response.text)

    except requests.RequestException as e:
        print(f"Failed to send image: {e}")

# FUNÇÕES DE CÂMARA (OPENCV)

def find_camera(max_index=4):
    """
    Tenta detetar a primeira câmara disponível iterando índices.
    Retorna (índice, objeto_cap) ou (None, None).
    """
    for i in range(max_index):
        # cv2.CAP_DSHOW é específico para Windows (DirectShow) para arranque rápido.
        # Se estiveres em Linux/Mac, podes remover o segundo argumento se der erro.
        cap = cv2.VideoCapture(i, cv2.CAP_DSHOW)
        
        if cap.isOpened():
            print(f"Camera detected at index {i}")
            return i, cap
        
        cap.release()
    
    return None, None


def capture_frame(cap):
    """
    Configura a resolução e captura um frame da câmara.
    """
    # Define a resolução desejada
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)

    ret, frame = cap.read()
    
    if not ret:
        print("Failed to capture image from webcam.")
        return None
        
    return frame


def encode_image(frame):
    """
    Codifica a matriz da imagem (numpy array) para bytes JPEG.
    """
    ret, buffer = cv2.imencode(".jpg", frame)
    
    if not ret:
        print("Failed to encode image.")
        return None
        
    return buffer.tobytes()

# MAIN LOOP

def main():
    print(f"Waiting for server at {URL}...")

    # 1. Tenta conectar ao servidor antes de iniciar a câmara
    retries = 0
    while not is_server_alive():
        retries += 1
        print(f"Server not responding. Retry {retries}/{MAX_RETRIES}...")
        
        if retries >= MAX_RETRIES:
            sys.exit("Max retries reached. Server is offline. Exiting.")
        
        time.sleep(PING_INTERVAL)

    print("Server is online. Starting camera uploader.")

    # 2. Inicializa a câmara
    camera_index, cap = find_camera()
    if camera_index is None:
        sys.exit("No camera detected. Exiting.")

    # Bloco try/finally para garantir que a câmara é libertada se houver erro ou Ctrl+C
    try:
        while True:
            # Verifica se o servidor ainda está vivo antes de processar imagem
            if not is_server_alive():
                print("Server went offline. Stopping camera uploader.")
                break

            # Captura
            frame = capture_frame(cap)
            if frame is not None:
                # Codifica
                image_bytes = encode_image(frame)
                if image_bytes is not None:
                    # Envia
                    send_to_api(image_bytes)

            # Espera até à próxima captura
            time.sleep(INTERVAL)

    except KeyboardInterrupt:
        print("\nStopping script defined by user...")
    
    finally:
        # Liberta o recurso da câmara
        if cap is not None and cap.isOpened():
            cap.release()
            print("Camera resource released.")

# Ponto de entrada
if __name__ == "__main__":
    main()