<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['Id_categoria'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID de la categoría"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categoria SET activo = FALSE WHERE Id_categoria = ?");
    $stmt->execute([$data['Id_categoria']]);

    echo json_encode(["success" => true, "message" => "Categoría desactivada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
