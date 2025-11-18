<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['Nombre_Categoria'])) {
    echo json_encode(["success" => false, "message" => "Falta el nombre de la categoría"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO categoria (Nombre_Categoria, activo) VALUES (?, TRUE)");
    $stmt->execute([$data['Nombre_Categoria']]);

    echo json_encode([
        "success" => true,
        "message" => "Categoría agregada correctamente"
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
