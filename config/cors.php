<?php
// Configurar CORS para permitir peticiones desde cualquier origen
// IMPORTANTE: Este archivo debe ser requerido ANTES de cualquier otro header o output
// NO debe haber ningún output antes de este archivo (ni espacios, ni saltos de línea)

// Evitar cualquier output antes de los headers
if (ob_get_level()) {
    ob_clean();
}

// Manejar preflight OPTIONS PRIMERO (las peticiones de pre-vuelo del navegador)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    // Headers CORS para preflight
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Max-Age: 86400");
    
    // El navegador está verificando si puede hacer la petición
    http_response_code(200);
    exit();
}

// Obtener el origen de la petición
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';

// En producción, deberías validar el origen contra una lista de dominios permitidos
// Ejemplo para producción:
// $allowedOrigins = ['https://tudominio.com', 'https://www.tudominio.com', 'http://127.0.0.1:5500'];
// if (in_array($origin, $allowedOrigins)) {
//     header("Access-Control-Allow-Origin: $origin");
//     header("Access-Control-Allow-Credentials: true");
// } else {
//     header("Access-Control-Allow-Origin: *");
// }

// Por ahora, permitir cualquier origen (útil para desarrollo y producción temporal)
header("Access-Control-Allow-Origin: *");

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");

// Headers permitidos en las peticiones
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

// Nota: Access-Control-Allow-Credentials no puede usarse con Access-Control-Allow-Origin: *
// Si necesitas credenciales, descomenta las líneas de arriba y ajusta los orígenes permitidos

// Cache preflight requests por 24 horas (86400 segundos)
header("Access-Control-Max-Age: 86400");
?>
