<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Control y Monitorización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        #temp, #humedad {
            font-size: 24px;
            margin: 20px;
        }
        button {
            padding: 10px 20px;
            font-size: 18px;
        }
        .error {
            color: red;
        }
        .menu {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="menu">
    <a href="eventos.php" target="_blank">Ver Eventos y Variables</a>
</div>
<hr>


<h1>Control y Monitorización del ESP32</h1>

<p id="temp">Temperatura: -- °C</p>
<p id="humedad">Humedad: -- %</p>

<p id="status" class="error"></p>

<button id="encender">Encender LED</button>
<button id="apagar">Apagar LED</button>

<script>
    let ws;
    const statusElement = document.getElementById('status');
    
    // Función para establecer la conexión WebSocket
    function connectWebSocket() {
        //ws = new WebSocket('ws://localhost:35350');
        ws = new WebSocket('ws://192.168.204.238:35350');

        ws.onopen = () => {
            console.log('Conectado al servidor WebSocket');
            statusElement.innerText = ''; // Limpiar el estado de error al conectar
        };

        ws.onmessage = (event) => {
            // Recibir datos de temperatura y humedad del servidor
            const [temp, humedad] = event.data.split('/');
            document.getElementById('temp').innerText = `Temperatura: ${temp} °C`;
            document.getElementById('humedad').innerText = `Humedad: ${humedad} %`;
        };

        ws.onclose = () => {
            console.log('Conexión cerrada, intentando reconectar...');
            statusElement.innerText = 'Conexión cerrada, intentando reconectar...';
            setTimeout(connectWebSocket, 3000);  // Intentar reconectar cada 3 segundos
        };

        ws.onerror = (error) => {
            console.error('Error en WebSocket:', error);
            statusElement.innerText = 'Error en WebSocket, revisa la consola para más detalles.';
        };
    }

    // Conectar al WebSocket cuando se cargue la página
    connectWebSocket();

    // Botones para encender y apagar el LED
    document.getElementById('encender').addEventListener('click', () => {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send('1');  // Enviar comando para encender el LED
        } else {
            statusElement.innerText = 'No conectado al WebSocket, no se puede enviar comando.';
        }
    });

    document.getElementById('apagar').addEventListener('click', () => {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send('0');  // Enviar comando para apagar el LED
        } else {
            statusElement.innerText = 'No conectado al WebSocket, no se puede enviar comando.';
        }
    });
</script>

</body>
</html>
