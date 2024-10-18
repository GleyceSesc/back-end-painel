<?php
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: true");
    http_response_code(200);
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, DELETE, POST, PUT, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

// Adicione os cabeçalhos CORS para todas as solicitações
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-Requested-With, application/json');

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */
$minPhpVersion = '8.1'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;
    exit(1);
}
/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */
// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}
/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */
// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder
$paths = new Config\Paths();
// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';
exit(CodeIgniter\Boot::bootWeb($paths));
