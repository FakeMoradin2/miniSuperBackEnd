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
    $campos = [];
    $valores = [];

    if (isset($data['nombre_proveedor'])) {
        $campos[] = "nombre_proveedor = ?";
        $valores[] = $data['nombre_proveedor'];
    }
    if (isset($data['telefono_proveedor'])) {
        $campos[] = "telefono_proveedor = ?";
        $valores[] = $data['telefono_proveedor'];
    }
    if (isset($data['correo_proveedor'])) {
        $campos[] = "correo_proveedor = ?";
        $valores[] = $data['correo_proveedor'];
    }
    if (isset($data['activo_proveedor'])) {
        $campos[] = "activo_proveedor = ?";
        $valores[] = $data['activo_proveedor'];
    }

    if (empty($campos)) {
        echo json_encode(["success" => false, "message" => "No hay campos para actualizar"]);
        exit;
    }

    $valores[] = $data['Id_proveedor'];

    $sql = "UPDATE proveedor SET " . implode(", ", $campos) . " WHERE Id_proveedor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    echo json_encode(["success" => true, "message" => "Proveedor actualizado correctamente"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
