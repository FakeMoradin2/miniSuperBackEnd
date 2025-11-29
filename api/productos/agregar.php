<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

// Leer datos enviados desde el body
$data = json_decode(file_get_contents("php://input"), true);

// Validar campos requeridos
if (
    !isset($data['nombre_producto'], $data['precio'], $data['stock'], 
            $data['categoria_id'], $data['proveedor_id'], $data['image_url'])
) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

try {
    // Preparar query
    $stmt = $pdo->prepare("
        INSERT INTO producto (nombre_producto, precio, stock, categoria_id, proveedor_id, activo_producto, image_url)
        VALUES (?, ?, ?, ?, ?, TRUE, ?)
    ");

    $stmt->execute([
        $data['nombre_producto'],
        $data['precio'],
        $data['stock'],
        $data['categoria_id'],
        $data['proveedor_id'],
        $data['image_url']
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Producto agregado correctamente"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
