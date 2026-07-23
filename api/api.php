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
        } else {
            handlePost($pdo, $input);
        }
        break;
    case 'PUT':
        handlePut($pdo, $input);
        break;
    case 'DELETE':
        handleDelete($pdo, $input);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGet($pdo)
{
    $query = "SELECT * FROM usuarios";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
}

function handlePost($pdo, $input)
{
    $check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo");
    $check->execute([':correo' => $input['correo']]);
    if ($check->fetchColumn() > 0) {
        echo "Este correo ya esta registrado", $input['accion'];
        return;
    }
    $query = "INSERT INTO usuarios (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)";
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
    $stmt = $pdo->prepare("SELECT idUsuarios, nombre, correo, contrasena FROM usuarios WHERE correo = :correo");
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


function handlePut($pdo, $input)
{
    $query = "UPDATE usuarios SET nombre = :nombre, correo = :correo, contrasena = :contrasena WHERE idUsuarios = :idUsuarios";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'idUsuarios' => $input['idUsuarios'],
        'nombre' => $input['nombre'],
        'correo' => $input['correo'],
        'contrasena' => $input['contrasena'],
    ]);

    echo json_encode(['message' => 'Post actualizado exitosamente']);
}

function handleDelete($pdo, $input)
{
    $query = "DELETE FROM usuarios WHERE idUsuarios = :idUsuarios";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['idUsuarios' => $input['idUsuarios'],]);

    echo json_encode(['message' => 'Post eliminado exitosamente']);
}
