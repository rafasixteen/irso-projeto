from gpio import *
from time import *

pinMode(0, IN)
pinMode(1, OUT)


def main():
    speed = "2"
    firstLoop = False

    while True:
        state = digitalRead(0) == 1023  # Verifica se está ligado

        if state:
            if not firstLoop:
                firstLoop = True
                print("First Loop")
                if speed == "2":
                    speed = "1"
                else:
                    speed = "2"
        else:
            firstLoop = False

        if state:
            customWrite(1, speed)
        else:
            customWrite(1, "0")

        print(state)

        sleep(0.1)


if __name__ == "__main__":
    main()
