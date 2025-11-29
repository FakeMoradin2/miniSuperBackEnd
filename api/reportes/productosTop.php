<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

// Leer parámetros opcionales de fechas
$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : null;
$fecha_fin = isset($_GET['fin']) ? $_GET['fin'] : null;

try {
    $sql = "
        SELECT 
            p.producto_id,
            p.nombre_producto,
            c.Nombre_Categoria AS categoria,
            SUM(t.cantidad) AS total_vendido,
            SUM(t.subtotal) AS ingresos_generados
        FROM ticket t
        INNER JOIN producto p ON p.producto_id = t.producto_id
        INNER JOIN venta v ON v.id_venta = t.venta_id
        LEFT JOIN categoria c ON p.categoria_id = c.Id_categoria
        WHERE v.estado_venta = 'completada'
    ";

    // Aplicar rango de fechas si existe
    if ($fecha_inicio && $fecha_fin) {
        $sql .= " AND DATE(v.creada_en_venta) BETWEEN :inicio AND :fin";
    } elseif ($fecha_inicio) {
        $sql .= " AND DATE(v.creada_en_venta) = :inicio";
    }

    $sql .= "
        GROUP BY p.producto_id, p.nombre_producto, c.Nombre_Categoria
        ORDER BY total_vendido DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql);

    if ($fecha_inicio && $fecha_fin) {
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
    } elseif ($fecha_inicio) {
        $stmt->bindParam(':inicio', $fecha_inicio);
    }

    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => count($productos),
        "data" => $productos
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
