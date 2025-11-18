<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['venta_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID de la venta"]);
    exit;
}

try {
    // Calcular total
    $stmt = $pdo->prepare("SELECT SUM(subtotal) AS total FROM ticket WHERE venta_id = ?");
    $stmt->execute([$data['venta_id']]);
    $total = $stmt->fetchColumn();

    // Actualizar venta
    $stmt = $pdo->prepare("
        UPDATE venta
        SET estado_venta = 'completada', total = ?, creada_en_venta = NOW()
        WHERE id_venta = ?
    ");
    $stmt->execute([$total, $data['venta_id']]);

    echo json_encode([
        "success" => true,
        "message" => "Venta confirmada correctamente",
        "total" => $total
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
