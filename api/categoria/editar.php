<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['Id_categoria'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID de la categoría"]);
    exit;
}

try {
    $campos = [];
    $valores = [];

    if (isset($data['Nombre_Categoria'])) {
        $campos[] = "Nombre_Categoria = ?";
        $valores[] = $data['Nombre_Categoria'];
    }

    if (isset($data['activo'])) {
        $campos[] = "activo = ?";
        $valores[] = $data['activo'];
    }

    if (empty($campos)) {
        echo json_encode(["success" => false, "message" => "No hay campos para actualizar"]);
        exit;
    }

    $valores[] = $data['Id_categoria'];

    $sql = "UPDATE categoria SET " . implode(", ", $campos) . " WHERE Id_categoria = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    echo json_encode(["success" => true, "message" => "Categoría actualizada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
