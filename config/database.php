<?php
// IMPORTANTE: Este archivo debe requerirse DESPUÉS de cors.php
// para que los headers CORS estén configurados antes de cualquier error

$host = 'database-2.cfic6ma02we7.us-east-2.rds.amazonaws.com';
$db   = 'minisuper';
$user = 'admin';
$pass = 'Saltamontes1.';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "✅ Conectado correctamente";
} catch (PDOException $e) {
    // Asegurar que Content-Type esté configurado antes del error
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}
?>
