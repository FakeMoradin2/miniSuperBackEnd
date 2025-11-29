<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

// Validar campos obligatorios
if (!isset($data['producto_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del producto"]);
    exit;
}

try {
    // Construir dinámicamente los campos a actualizar
    $campos = [];
    $valores = [];

    if (isset($data['nombre_producto'])) {
        $campos[] = "nombre_producto = ?";
        $valores[] = $data['nombre_producto'];
    }
    if (isset($data['precio'])) {
        $campos[] = "precio = ?";
        $valores[] = $data['precio'];
    }
    if (isset($data['stock'])) {
        $campos[] = "stock = ?";
        $valores[] = $data['stock'];
    }
    if (isset($data['categoria_id'])) {
        $campos[] = "categoria_id = ?";
        $valores[] = $data['categoria_id'];
    }
    if (isset($data['proveedor_id'])) {
        $campos[] = "proveedor_id = ?";
        $valores[] = $data['proveedor_id'];
    }
    if(isset($data['image_url']))
    {
        $campos[] = "image_url = ?";
        $valores[] = $data['image_url'];
    }
    if (isset($data['activo_producto'])) {
        $campos[] = "activo_producto = ?";
        $valores[] = $data['activo_producto'];
    }

    if (empty($campos)) {
        echo json_encode(["success" => false, "message" => "No hay campos para actualizar"]);
        exit;
    }

    $valores[] = $data['producto_id'];
    $sql = "UPDATE producto SET " . implode(", ", $campos) . " WHERE producto_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    echo json_encode(["success" => true, "message" => "Producto actualizado correctamente"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
