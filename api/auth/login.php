<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['telefono'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

try {
    // Buscar usuario por teléfono
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE telefono = ?");
    $stmt->execute([$data['telefono']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit;
    }

    // Verificar contraseña
    if (!password_verify($data['password'], $usuario['password'])) {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        exit;
    }

    // Crear token temporal
    $token = bin2hex(random_bytes(16));

    echo json_encode([
        "success" => true,
        "message" => "Inicio de sesión exitoso",
        "usuario" => [
            "id" => $usuario['usuario_id'],
            "nombre_usuario" => $usuario['nombre_usuario'],
            "telefono" => $usuario['telefono'],
            "rol" => $usuario['rol']
        ],
        "token" => $token
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
