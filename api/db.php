<?php
    $host = "prueba-321-puraclase-123123123.i.aivencloud.com";
    $user = "avnadmin";
    $password = "AVNS_VpDydtgOXJtnVnejiTr";
    $dbname = "defaultdb";
    $port = "12049";

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Conexion fallida: ". $e->getMessage());
    }

?>