<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Control y Monitorización</title>
    <!-- Incluir el CDN de Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <script src="js/chart.js"></script>
    
    <!-- Incluir el CDN del adaptador de fechas para Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script> -->
    <script src="js/chartjs-adapter-date-fns.js"></script>

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
        /* Estilo para el canvas */
        #chart-container {
            width: 80%;
            margin: 20px auto;
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

<!-- Contenedor para la gráfica -->
<div id="chart-container">
    <canvas id="myChart"></canvas>
</div>

<script>
    let ws;
    const statusElement = document.getElementById('status');
    
    // Inicializar la gráfica usando Chart.js
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: 'Temperatura (°C)',
                    data: [],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: false
                },
                {
                    label: 'Humedad (%)',
                    data: [],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return `${tooltipItem.dataset.label}: ${tooltipItem.raw.y} (${tooltipItem.raw.x.toLocaleTimeString()})`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'second',
                        tooltipFormat: 'll HH:mm:ss'
                    },
                    title: {
                        display: true,
                        text: 'Fecha y Hora'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Valor'
                    }
                }
            }
        }
    });

    // Función para establecer la conexión WebSocket
    function connectWebSocket() {
      //  ws = new WebSocket('ws://localhost:35350');
        ws = new WebSocket('ws://192.168.204.238:35350');

        ws.onopen = () => {
            console.log('Conectado al servidor WebSocket');
            statusElement.innerText = ''; // Limpiar el estado de error al conectar
        };

        ws.onmessage = (event) => {
            console.log('Datos recibidos:', event.data); // Agregar un log para depuración
            // Recibir datos de temperatura y humedad del servidor
            const [temp, humedad] = event.data.split('/');
            document.getElementById('temp').innerText = `Temperatura: ${temp} °C`;
            document.getElementById('humedad').innerText = `Humedad: ${humedad} %`;

            // Verificar que los datos son números antes de agregarlos al gráfico
            if (!isNaN(temp) && !isNaN(humedad)) {
                // Agregar los datos a la gráfica
                const now = new Date();
                myChart.data.labels.push(now); // Agregar la fecha y hora actual como etiqueta
                myChart.data.datasets[0].data.push({x: now, y: parseFloat(temp)});
                myChart.data.datasets[1].data.push({x: now, y: parseFloat(humedad)});
                myChart.update();

                // Limitar la cantidad de puntos en la gráfica
                if (myChart.data.labels.length > 50) {  // Muestra los últimos 50 puntos
                    myChart.data.labels.shift();
                    myChart.data.datasets[0].data.shift();
                    myChart.data.datasets[1].data.shift();
                }
            }
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
