<?php
// Configurar CORS para permitir peticiones desde cualquier origen
// IMPORTANTE: Este archivo debe ser requerido ANTES de cualquier otro header o output

// Obtener el origen de la petición
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';

// En producción, deberías validar el origen contra una lista de dominios permitidos
// Ejemplo para producción:
// $allowedOrigins = ['https://tudominio.com', 'https://www.tudominio.com'];
// if (in_array($origin, $allowedOrigins)) {
//     header("Access-Control-Allow-Origin: $origin");
// }

// Por ahora, permitir cualquier origen (útil para desarrollo)
header("Access-Control-Allow-Origin: *");

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");

// Headers permitidos en las peticiones
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

// Nota: Access-Control-Allow-Credentials no puede usarse con Access-Control-Allow-Origin: *
// Si necesitas credenciales, descomenta las siguientes líneas y ajusta los orígenes permitidos arriba
// header("Access-Control-Allow-Credentials: true");

// Cache preflight requests por 24 horas (86400 segundos)
header("Access-Control-Max-Age: 86400");

// Manejar preflight OPTIONS (las peticiones de pre-vuelo del navegador)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    // El navegador está verificando si puede hacer la petición
    http_response_code(200);
    exit();
}
?>
