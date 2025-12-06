<?php
// === CORS - debe ir ANTES de cualquier salida ===
$allowedOrigin = "http://localhost:4200"; // si usas ng serve
// $allowedOrigin = "http://localhost:5173"; // si usas Vite

header("Access-Control-Allow-Origin: $allowedOrigin");
header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Credentials: true"); // solo si usas sesiones/cookies

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// DEBUG: mostrar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();


// === Requerir archivos principales ===
require_once 'cxn/Connection.php';
require_once 'ctrl/core/commun/Request.php';
require_once 'ctrl/core/commun/IRequest.php';
require_once 'ctrl/core/segurity/Users.php';
require_once 'ctrl/core/segurity/UserAction.php';
require_once 'ctrl/core/segurity/Roles.php';
require_once 'view/ViewAPI.php';
require_once 'view/ViewXML.php';
require_once 'view/ViewJSON.php';
require_once 'util/ExcepcionAPI.php';
require_once 'util/Status.php';
require_once 'util/MessageUser.php';
require_once 'util/FormatType.php';
require_once 'util/ContentBody.php';
require_once 'util/ResourcesURL.php';
require_once 'util/JSONUtil.php';
require_once 'model/core/segurity/User.php';
require_once 'querys/core/SegurityQuery.php';

// Business
require_once 'ctrl/business/Persons.php';
require_once 'model/business/Person.php';
require_once 'querys/business/BusinessQuery.php';
require_once 'ctrl/business/DocumentType.php';
require_once 'model/business/DocumentTypeModel.php';

// === Resto del flujo Panther ===
// ... (todo tu código de manejo de PATH_INFO y login/register)

// === Formato de salida ===
$format = isset($_GET['FORMAT']) ? $_GET['FORMAT'] : 'JSON';
switch ($format) {
    case 'XML':
        $view = new ViewXML();
        break;
    case 'JSON':
    default:
        $view = new ViewJSON();
}

// === Manejo global de excepciones para devolver JSON consistente ===
set_exception_handler(function ($exception) use ($view) {
    $bodyAnswer = new ContentBody(INTERNAL_SERVER_ERROR, ST500, [
        "message" => $exception->getMessage(),
        "line" => $exception->getLine(),
        "file" => $exception->getFile()
        // no incluir trace en produccion
    ]);
    $view->viewPrint($bodyAnswer);
});

// === Extraer PATH_INFO (recurso) ===
if (isset($_GET['PATH_INFO'])) {
    $request = explode('/', trim($_GET['PATH_INFO'], '/'));
} else {
    throw new ExcepcionAPI(BAD_REQUEST, ST400, error_url);
}

$resource = array_shift($request);
$resourcesExisting = RESOURCES_URL;

// Validar recurso
if (!in_array(strtolower($resource), $resourcesExisting)) {
    throw new ExcepcionAPI(NOT_FOUND, ST404, error_notExist);
}

$className = ucfirst(strtolower($resource));
$method = strtolower($_SERVER['REQUEST_METHOD']);

// === Manejo EXCEPCIONAL para login/register en users ===
if ($resource === 'users' && isset($request[0])) {
    $action = strtolower($request[0]);
    // Nota: UserAction::login/register esperan JSON en el body
    if ($action === 'login' && $method === 'post') {
        $users = new Users();
        $answer = $users->login();  // <- sin pasar ContentBody::getBodyArray()
        $view->viewPrint($answer);
        ob_end_flush();
        exit;
    }

    if ($action === 'register' && $method === 'post') {
        $users = new Users();
        $answer = $users->register(); // <- register tampoco necesita getBodyArray
        $view->viewPrint($answer);
        ob_end_flush();
        exit;
    }
}

// === Flujo normal Panther ===
switch ($method) {
    case 'get':
    case 'post':
    case 'put':
    case 'delete':
        if (class_exists($className) && method_exists($className, $method)) {
            $instance = new $className();
            if (method_exists($instance, 'init')) {
                $instance->init();
            }
            $answer = $instance->$method($request);
            $view->viewPrint($answer);
        } else {
            throw new ExcepcionAPI(BAD_REQUEST, ST400, error_url);
        }
        break;
    default:
        throw new ExcepcionAPI(BAD_REQUEST, ST400, error_url);
}

// flush output buffer
ob_end_flush();
?>