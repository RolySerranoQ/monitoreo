<?php 
$contrasena = "r1o2l3y4.";
$usuario = "root";
$nombre_bd = "db_taller";  // Nombre de la BDD

try {
    $bd = new PDO (
        'mysql:host=localhost;dbname='.$nombre_bd,
        $usuario,
        $contrasena,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
} catch (Exception $e) {
    echo "Problema con la conexión: ".$e->getMessage();
}
?>
