<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Leer parámetros GET opcionales (fechas)
$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : null;
$fecha_fin = isset($_GET['fin']) ? $_GET['fin'] : null;

try {
    $sql = "
        SELECT 
            DATE(v.creada_en_venta) AS fecha,
            COUNT(*) AS total_ventas,
            SUM(v.total) AS monto_total,
            AVG(v.total) AS promedio_venta
        FROM venta v
        WHERE v.estado_venta = 'completada'
    ";

    // Filtros por fecha (rango o día)
    if ($fecha_inicio && $fecha_fin) {
        $sql .= " AND DATE(v.creada_en_venta) BETWEEN :inicio AND :fin";
    } elseif ($fecha_inicio) {
        $sql .= " AND DATE(v.creada_en_venta) = :inicio";
    }

    $sql .= " GROUP BY DATE(v.creada_en_venta)
              ORDER BY fecha DESC";

    $stmt = $pdo->prepare($sql);

    if ($fecha_inicio && $fecha_fin) {
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
    } elseif ($fecha_inicio) {
        $stmt->bindParam(':inicio', $fecha_inicio);
    }

    $stmt->execute();
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total_dias" => count($reportes),
        "data" => $reportes
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
