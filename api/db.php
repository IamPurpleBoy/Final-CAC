<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "movies_cac";


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Conectado exitosamente.";
} 
catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

?>