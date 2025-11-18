<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Leer parámetros opcionales
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
$rol = isset($_GET['rol']) ? $_GET['rol'] : null; // 'cliente', 'cajero' o 'admin'

try {
    // Base query con joins
    $sql = "
        SELECT 
            v.id_venta,
            v.estado_venta,
            v.total,
            v.creada_en_venta,
            u1.nombre_usuario AS comprador,
            u2.nombre_usuario AS vendedor
        FROM venta v
        LEFT JOIN usuario u1 ON v.comprador_id = u1.usuario_id
        LEFT JOIN usuario u2 ON v.vendedor_id = u2.usuario_id
        WHERE 1=1
    ";

    // Filtros opcionales
    if ($rol === 'cliente' && $usuario_id) {
        $sql .= " AND v.comprador_id = :usuario_id";
    } elseif ($rol === 'cajero' && $usuario_id) {
        $sql .= " AND v.vendedor_id = :usuario_id";
    }

    $sql .= " ORDER BY v.creada_en_venta DESC";

    $stmt = $pdo->prepare($sql);

    if ($usuario_id) {
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => count($ventas),
        "data" => $ventas
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
