<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['venta_id'], $data['producto_id'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos: venta_id y producto_id"]);
    exit;
}

try {
    // Verificar que la venta siga siendo carrito
    $stmt = $pdo->prepare("SELECT estado_venta FROM venta WHERE id_venta = ?");
    $stmt->execute([$data['venta_id']]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta || $venta['estado_venta'] !== 'carrito') {
        echo json_encode(["success" => false, "message" => "Solo se pueden modificar carritos activos"]);
        exit;
    }

    // Eliminar producto del carrito
    $stmt = $pdo->prepare("DELETE FROM ticket WHERE venta_id = ? AND producto_id = ?");
    $stmt->execute([$data['venta_id'], $data['producto_id']]);

    echo json_encode(["success" => true, "message" => "Producto eliminado del carrito"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
