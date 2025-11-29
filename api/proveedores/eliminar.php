<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['Id_proveedor'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del proveedor"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE proveedor SET activo_proveedor = FALSE WHERE Id_proveedor = ?");
    $stmt->execute([$data['Id_proveedor']]);

    echo json_encode(["success" => true, "message" => "Proveedor desactivado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
