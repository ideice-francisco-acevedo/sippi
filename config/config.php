<?php
define('APP_NAME', 'SIPPI - IDEICE');
define('APP_VERSION', '2.0');
define('BASE_URL', 'http://localhost/sippi/');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sippi_ideice');

date_default_timezone_set('America/Santo_Domingo');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

session_start();

// Configuración de errores (ocultar notices y warnings en producción)
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/../includes/functions.php';
?>