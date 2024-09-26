<?php
// Incluir el archivo de conexión PDO
include 'conexion/conexionPDO.php';

try {
    // Consulta a la tabla 'variables'
    $consultaVariables = $bd->query("SELECT * FROM variables");
    //$consultaVariables = $bd->query("SELECT * FROM variables WHERE DATE(FECHA) = CURDATE()");


    $variables = $consultaVariables->fetchAll(PDO::FETCH_ASSOC);

    // Consulta a la tabla 'eventos'
    $consultaEventos = $bd->query("SELECT * FROM eventos");
    //$consultaEventos = $bd->query("SELECT * FROM eventos WHERE DATE(FECHA) = CURDATE()");
    $eventos = $consultaEventos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error al realizar la consulta: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos y Variables</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 45%;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Variables Monitoreadas</h2>
<table>
    <thead>
        <tr>
            <!-- estructura de la tabla 'variables' -->
            <th>ID</th>
            <th>Temperatura</th>
            <th>Humedad</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($variables as $variable): ?>
        <tr>
            <td><?php echo htmlspecialchars($variable['ID']); ?></td>
            <td><?php echo htmlspecialchars($variable['TEMP']); ?></td>
            <td><?php echo htmlspecialchars($variable['HUMEDAD']); ?></td>
            <td><?php echo htmlspecialchars($variable['FECHA']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Eventos</h2>
<table>
    <thead>
        <tr>
            <!-- estructura de la tabla 'eventos' -->
            <th>ID</th>
            <th>Evento</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($eventos as $evento): ?>
        <tr>
            <td><?php echo htmlspecialchars($evento['ID']); ?></td>
            <td><?php echo htmlspecialchars($evento['EVENTO']); ?></td>
            <td><?php echo htmlspecialchars($evento['FECHA']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
