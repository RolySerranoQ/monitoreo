import socket
import asyncio
import websockets
from conexion import conectar, cerrar_conexion
import datetime as dt

# Configuración del servidor TCP y WebSocket
HOST = '0.0.0.0'  # Escuchar en todas las interfaces de red
PORT = 32320      # Puerto de escucha TCP
WS_HOST = '0.0.0.0'
WS_PORT = 35350   # Puerto de escucha WebSocket

salir = False
ws_clients = set()  # Almacenar clientes WebSocket conectados
 
writer_global = None

# Función para manejar los WebSockets


async def websocket_handler(websocket, path):
    ws_clients.add(websocket)
    try:
        async for message in websocket:   # para enviar mensaje del panel de control (botones)
            if message == "1":
                # Enviar comando para encender el LED al ESP32
                await send_to_esp32(writer_global, b"1\n")
                insertar_evento("Encender LED desde Web")
            elif message == "0":
                # Enviar comando para apagar el LED al ESP32
                await send_to_esp32(writer_global, b"0\n")
                insertar_evento("Apagar LED desde Web")
    except websockets.exceptions.ConnectionClosed:
        print("Cliente WebSocket desconectado")
    finally:
        ws_clients.remove(websocket)


# Función para manejar la recepción de datos del ESP32 de forma asíncrona
async def handle_client(reader, writer):
    global writer_global
    writer_global = writer

    addr = writer.get_extra_info('peername')
    print(f"Conexión establecida con {addr[0]}:{addr[1]}")

    # Obtener la dirección IP y el puerto del ESP32
    ip_esp32 = addr[0]
    puerto_esp32 = addr[1]

    try:
        while not salir:
            data = await reader.read(1024)
            if not data:
                break
            temp, humedad = data.decode('utf-8').split('/')
            insertar_datos(float(temp), float(humedad))
            await broadcast_data(temp, humedad)    #  brodcast de los datos (de sockets a websocket)
    except Exception as e:
        print(f"Error con {addr}: {e}")
    finally:
        print(f"Conexión cerrada con {addr}")
        writer.close()
        await writer.wait_closed()


# Función para enviar datos a todos los clientes WebSocket conectados
async def broadcast_data(temp, humedad):
    if ws_clients:
        message = f"{temp}/{humedad}"
        await asyncio.gather(*[client.send(message) for client in ws_clients])


# Función para enviar datos al ESP32
async def send_to_esp32(writer, message):
    try:
        writer.write(message)
        await writer.drain()
    except Exception as e:
        print(f"Error al enviar datos al ESP32: {e}")


# Función para insertar los datos de temperatura y humedad en la base de datos
def insertar_datos(temp, humedad):
    conexion = conectar()
    if conexion:
        try:
            cursor = conexion.cursor()
            query = "INSERT INTO variables (TEMP, HUMEDAD) VALUES (%s, %s)"
            cursor.execute(query, (temp, humedad))
            conexion.commit()
            fecha = dt.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            print(f"Datos insertados: Temperatura={
                  temp}, Humedad={humedad}, -{fecha}")
        except Exception as e:
            print(f"Error al insertar los datos: {e}")
        finally:
            cerrar_conexion(conexion)


# Función para insertar eventos en la base de datos
def insertar_evento(evento):
    conexion = conectar()
    if conexion:
        try:
            cursor = conexion.cursor()
            query = "INSERT INTO eventos (EVENTO) VALUES (%s)"
            cursor.execute(query, (evento,))
            conexion.commit()
            fecha = dt.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            print(f"EVENTO insertado: {evento}, -{fecha}")
        except Exception as e:
            print(f"Error al insertar el evento: {e}")
        finally:
            cerrar_conexion(conexion)


async def main():
    # Iniciar el servidor TCP usando asyncio
    server_tcp = await asyncio.start_server(handle_client, HOST, PORT)
    print(f"Servidor TCP esperando conexiones en {HOST}:{PORT}")

    # Iniciar el servidor WebSocket
    start_server = websockets.serve(websocket_handler, WS_HOST, WS_PORT)
    await start_server
    print(f"Servidor WebSocket iniciado en {WS_HOST}:{WS_PORT}")

    async with server_tcp:
        await server_tcp.serve_forever()


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("Cerrando servidores...")
