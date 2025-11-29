<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Leer datos del body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['comprador_id'], $data['vendedor_id'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

$comprador_id = $data['comprador_id'];
$vendedor_id = $data['vendedor_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id_venta 
        FROM venta 
        WHERE comprador_id = ? AND is_carrito = 1 
        LIMIT 1
    ");
    $stmt->execute([$comprador_id]);
    $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($carrito) {
        echo json_encode([
            "success" => true,
            "message" => "Carrito ya existente",
            "venta_id" => $carrito["id_venta"]
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO venta (comprador_id, vendedor_id, estado_venta, total)
        VALUES (?, ?, 'carrito', 0)
    ");
    $stmt->execute([$comprador_id, $vendedor_id]);

    echo json_encode([
        "success" => true,
        "message" => "Carrito creado",
        "venta_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
