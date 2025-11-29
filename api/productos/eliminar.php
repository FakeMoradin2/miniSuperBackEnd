<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

// Leer datos del body (JSON)
$data = json_decode(file_get_contents("php://input"), true);

// Validar que se haya enviado el ID
if (!isset($data['producto_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del producto"]);
    exit;
}

try {
    // Marcar producto como inactivo
    $stmt = $pdo->prepare("UPDATE producto SET activo_producto = FALSE WHERE producto_id = ?");
    $stmt->execute([$data['producto_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Producto desactivado correctamente"]);
    } else {
        echo json_encode(["success" => false, "message" => "No se encontró el producto especificado"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
