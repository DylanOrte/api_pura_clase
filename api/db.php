<?php
    $host = "prueba-321-puraclase-123123123.i.aivencloud.com";
    $user = "avnadmin";
    $password = "AVNS_VpDydtgOXJtnVnejiTr";
    $dbname = "defaultdb";
    $port = "12049";

    // Con mysqli
    $mysqli = mysqli_init();
    // Use correct path to CA file and defined variables
    $caFile = __DIR__ . '/ca.pem';
    if (!file_exists($caFile)) {
        // fallback to relative path
        $caFile = __DIR__ . "\\ca.pem";
    }
    mysqli_ssl_set($mysqli, NULL, NULL, $caFile, NULL, NULL);
    mysqli_real_connect($mysqli, $host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Conexion fallida: ". $e->getMessage());
    }

?>