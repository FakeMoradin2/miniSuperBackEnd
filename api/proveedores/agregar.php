<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nombre_proveedor'])) {
    echo json_encode(["success" => false, "message" => "Falta el nombre del proveedor"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO proveedor (nombre_proveedor, telefono_proveedor, correo_proveedor, activo_proveedor) 
                           VALUES (?, ?, ?, TRUE)");
    $stmt->execute([
        $data['nombre_proveedor'],
        $data['telefono_proveedor'] ?? null,
        $data['correo_proveedor'] ?? null
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Proveedor agregado correctamente"
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
