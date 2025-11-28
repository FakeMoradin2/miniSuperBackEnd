<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nombre_usuario'], $data['password'], $data['telefono'], $data['rol'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

try {
    // Verificar teléfono duplicado
    $stmt = $pdo->prepare("SELECT usuario_id FROM usuario WHERE telefono = ?");
    $stmt->execute([$data['telefono']]);
    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "message" => "El número de teléfono ya está registrado"]);
        exit;
    }

    // Hashear contraseña
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Insertar usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuario (nombre_usuario, password, telefono, rol)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['nombre_usuario'],
        $password,
        $data['telefono'],
        $data['rol']
    ]);

    echo json_encode(["success" => true, "message" => "Usuario registrado correctamente"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
