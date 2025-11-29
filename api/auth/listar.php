<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->prepare("
        SELECT usuario_id, nombre_usuario, telefono, rol, creado_en
        FROM usuario
        WHERE rol = 'cliente' OR rol = 'cajero'
        ORDER BY creado_en DESC
    ");
    $stmt->execute();

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => count($usuarios),
        "data" => $usuarios
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

?>