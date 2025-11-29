<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['venta_id'], $data['producto_id'], $data['cantidad'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

try {
    // Consultar precio actual del producto
    $stmt = $pdo->prepare("SELECT precio, stock FROM producto WHERE producto_id = ? AND activo_producto = TRUE");
    $stmt->execute([$data['producto_id']]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo json_encode(["success" => false, "message" => "Producto no encontrado o inactivo"]);
        exit;
    }

    if ($data['cantidad'] > $producto['stock']) {
        echo json_encode(["success" => false, "message" => "Stock insuficiente"]);
        exit;
    }

    $subtotal = $producto['precio'] * $data['cantidad'];

    // Insertar detalle (ticket)
    $stmt = $pdo->prepare("
        INSERT INTO ticket (venta_id, producto_id, cantidad, precio_unitario)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['venta_id'],
        $data['producto_id'],
        $data['cantidad'],
        $producto['precio'],
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Producto agregado al carrito correctamente",
        "subtotal" => $subtotal
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
