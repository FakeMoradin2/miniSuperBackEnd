<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['venta_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID de la venta"]);
    exit;
}

try {
    // Verificar que la venta exista y esté en estado carrito
    $stmt = $pdo->prepare("SELECT * FROM venta WHERE id_venta = ? AND estado_venta = 'carrito'");
    $stmt->execute([$data['venta_id']]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        echo json_encode(["success" => false, "message" => "Carrito no encontrado o ya completado/cancelado"]);
        exit;
    }

    // Cancelar el carrito
    $stmt = $pdo->prepare("UPDATE venta SET estado_venta = 'cancelada' WHERE id_venta = ?");
    $stmt->execute([$data['venta_id']]);

    echo json_encode(["success" => true, "message" => "Carrito cancelado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
