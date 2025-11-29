<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['venta_id'], $data['producto_id'], $data['cantidad'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos: venta_id, producto_id y cantidad"]);
    exit;
}

try {
    // Verificar que la venta siga activa como carrito
    $stmt = $pdo->prepare("SELECT estado_venta FROM venta WHERE id_venta = ?");
    $stmt->execute([$data['venta_id']]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta || $venta['estado_venta'] !== 'carrito') {
        echo json_encode(["success" => false, "message" => "Solo se pueden modificar carritos activos"]);
        exit;
    }

    // Verificar que el producto exista en el carrito
    $stmt = $pdo->prepare("SELECT cantidad, precio_unitario FROM ticket WHERE venta_id = ? AND producto_id = ?");
    $stmt->execute([$data['venta_id'], $data['producto_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(["success" => false, "message" => "El producto no está en el carrito"]);
        exit;
    }

    $nuevaCantidad = (int)$data['cantidad'];

    if ($nuevaCantidad <= 0) {
        // Si llega a 0 o menos, eliminar del carrito
        $stmt = $pdo->prepare("DELETE FROM ticket WHERE venta_id = ? AND producto_id = ?");
        $stmt->execute([$data['venta_id'], $data['producto_id']]);
        echo json_encode(["success" => true, "message" => "Producto eliminado del carrito"]);
        exit;
    }

    // Actualizar cantidad y subtotal
    $nuevoSubtotal = $item['precio_unitario'] * $nuevaCantidad;

    $stmt = $pdo->prepare("UPDATE ticket 
                           SET cantidad = ?, subtotal = ? 
                           WHERE venta_id = ? AND producto_id = ?");
    $stmt->execute([$nuevaCantidad, $nuevoSubtotal, $data['venta_id'], $data['producto_id']]);

    echo json_encode([
        "success" => true,
        "message" => "Cantidad actualizada correctamente",
        "nueva_cantidad" => $nuevaCantidad,
        "nuevo_subtotal" => $nuevoSubtotal
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
