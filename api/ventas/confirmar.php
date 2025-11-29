<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['venta_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID de la venta"]);
    exit;
}

$venta_id = $data['venta_id'];

try {
    // 1. Verificar si la venta existe
    $stmt = $pdo->prepare("SELECT * FROM venta WHERE id_venta = ?");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        echo json_encode(["success" => false, "message" => "La venta no existe"]);
        exit;
    }

    // 2. Evitar confirmar dos veces
    if ($venta['is_carrito'] == 0) {
        echo json_encode(["success" => false, "message" => "Esta venta ya está completada"]);
        exit;
    }

    // 3. Calcular total
    $stmt = $pdo->prepare("
        SELECT SUM(subtotal) AS total
        FROM ticket
        WHERE venta_id = ?
    ");
    $stmt->execute([$venta_id]);
    $total = $stmt->fetchColumn();

    if ($total === null) {
        echo json_encode(["success" => false, "message" => "El carrito está vacío"]);
        exit;
    }

    // 4. Actualizar venta
    $stmt = $pdo->prepare("
        UPDATE venta
        SET estado_venta = 'completada', total = ?
        WHERE id_venta = ?
    ");
    $stmt->execute([$total, $venta_id]);

    echo json_encode([
        "success" => true,
        "message" => "Venta confirmada correctamente",
        "total" => $total
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
