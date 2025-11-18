<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Leer parámetros opcionales (por ejemplo, ?categoria=3)
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

try {
    // Base query
    $sql = "SELECT 
                p.producto_id,
                p.nombre_producto,
                p.precio,
                p.stock,
                p.activo_producto,
                p.image_url,
                c.Nombre_Categoria AS categoria,
                pr.nombre_proveedor AS proveedor
            FROM producto p
            LEFT JOIN categoria c ON p.categoria_id = c.Id_categoria
            LEFT JOIN proveedor pr ON p.proveedor_id = pr.Id_proveedor
            WHERE p.activo_producto = TRUE";

    // Filtrar por categoría si se pasa parámetro
    if ($categoria) {
        $sql .= " AND p.categoria_id = :categoria";
    }

    $stmt = $pdo->prepare($sql);

    if ($categoria) {
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_INT);
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
