<?php

header('Content-Type: application/json');
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['accion'] ?? $input['accion'] ?? null;
switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        if ($action == 'login') {
            handleLogin($pdo, $input);
        } else if ($action == 'anadirLlave') {
            handleAnadirLlave($pdo, $input);
        } else if ($action == 'anadirPabellon') {
            handleAnadirPabellon($pdo, $input);
        }
        break;
    case 'PUT':
        handlePut($pdo, $input);
        break;
    case 'DELETE':
        if ($action == 'eliminarLlave') {
            handleDeleteLlave($pdo, $input);
        } else if ($action == 'eliminarPabellon') {
            handleDeletePabellon($pdo, $input);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGet($pdo)
{
    $stmt1 = $pdo->prepare("SELECT * FROM BDPuraClase.llaves");
    $stmt1->execute();
    $llaves = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT * FROM BDPuraClase.pabellones");
    $stmt2->execute();
    $pabellones = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'llaves' => $llaves,
        'pabellones' => $pabellones
    ];
    echo json_encode($response);
}

function handlePost($pdo, $input)
{
    $check = $pdo->prepare("SELECT COUNT(*) FROM BDPuraClase.usuarios WHERE correo = :correo");
    $check->execute([':correo' => $input['correo']]);
    if ($check->fetchColumn() > 0) {
        echo "Este correo ya esta registrado", $input['accion'];
        return;
    }
    $query = "INSERT INTO BDPuraClase.usuarios (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'nombre' => $input['nombre'],
        'correo' => $input['correo'],
        'contrasena' => $input['contrasena'],
    ]);
    echo 'Usuario creado exitosamente';
}

function handleLogin($pdo, $input)
{
    $stmt = $pdo->prepare("SELECT idUsuarios, nombre, correo, contrasena FROM BDPuraClase.usuarios WHERE correo = :correo");
    $stmt->execute([':correo' => $input['correo']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $input['contrasena'] != $user['contrasena']) {
        http_response_code(401);
        echo ('Correo o contraseña incorrecta');
        return;
    }

    unset($user['contrasena']);
    echo json_encode([
        'message' => 'Inicio de sesion exitoso',
        'proceder' => "si"
    ]);
}

function handleAnadirLlave($pdo, $input)
{
    $check = $pdo->prepare("SELECT COUNT(*) FROM BDPuraClase.llaves WHERE llave = :llave");
    $check->execute([':llave' => intVal($input['llave'])]);
    if ($check->fetchColumn() > 0) {
        echo json_encode("Esta llave ya existe");
        return;
    }
    $query = "INSERT INTO BDPuraClase.llaves (llave, pabellon) VALUES (:llave, :pabellon)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'llave' => intVal($input['llave']),
        'pabellon' => intVal($input['pabellon']),
    ]);
    echo json_encode("Llave agregada exitosamente");
}

function handleAnadirPabellon($pdo, $input) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM BDPuraClase.pabellones WHERE idpabellones = :idpabellones");
    $check->execute([':idpabellones' => intVal($input['idpabellones'])]);
    if ($check->fetchColumn() > 0) {
        echo json_encode("Este pabellon ya existe");
        return;
    }
    $query = "INSERT INTO BDPuraClase.pabellones (idpabellones) VALUES (:idpabellones)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'idpabellones' => intVal($input['idpabellones']),
    ]);
    echo json_encode("Pabellon agregado exitosamente");
}


function handlePut($pdo, $input)
{
    try {
        $query = "UPDATE BDPuraClase.llaves SET estado = :estado, profesor = :profesor WHERE llave = :llave";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'llave' => intval($input['llave']),
            'estado' => intval($input['estado']),
            'profesor' => $input['profesor'],
        ]);
        echo ("Llave actualizada exitosamente");
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}

function handleDeleteLlave($pdo, $input)
{
    $query = "DELETE FROM BDPuraClase.llaves WHERE llave = :llave";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['llave' => intVal($input['llave']),]);

    echo json_encode(['Llave eliminada exitosamente']);
}

function handleDeletePabellon($pdo, $input)
{
    $query = "DELETE FROM BDPuraClase.pabellones WHERE idpabellones = :idpabellones";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['idpabellones' => intVal($input['idpabellones']),]);

    echo json_encode(['Pabellon eliminado exitosamente']);
}
?>