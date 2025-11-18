<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Validar comprador y vendedor
if (!isset($data['comprador_id'], $data['vendedor_id'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

try {
    // Crear venta con estado 'carrito'
    $stmt = $pdo->prepare("
        INSERT INTO venta (comprador_id, vendedor_id, estado_venta, total)
        VALUES (?, ?, 'carrito', 0)
    ");
    $stmt->execute([$data['comprador_id'], $data['vendedor_id']]);

    echo json_encode([
        "success" => true,
        "message" => "Carrito creado correctamente",
        "venta_id" => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
