<?php
require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    "status" => "ok",
    "message" => "Servidor PHP conectado a AWS RDS correctamente"
]);
?>
