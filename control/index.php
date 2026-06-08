<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/DatabaseConfig.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/InputValidator.php';
require_once __DIR__ . '/JwtAuth.php';
require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/UsuarioController.php';
require_once __DIR__ . '/ObjetoController.php';
require_once __DIR__ . '/EmprestimoController.php';

try {
    $connection = DatabaseConfig::getConnection();
} catch (Exception $e) {
    ApiResponse::send(
        ApiResponse::error('Erro ao conectar ao banco de dados', 500)
    );
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^(.*/)?index\.php#', '', $path);
$path = rtrim($path, '/') ?: '/';

// Define quais rotas não precisam de token de login
$isPublicRoute = preg_match('~(/auth/login|/auth/register|/health)~', $path);

// Se não for pública, exige o login. Se for pública, deixa passar direto.
$auth = null;
if (!$isPublicRoute) {
    $auth = AuthMiddleware::authenticate();
}

switch (true) {
    // AUTH ROUTES
    case preg_match('~/auth/login~', $path) && $method === 'POST':
        $controller = new AuthController($connection);
        $controller->login();
        break;
        
    case preg_match('~/auth/register~', $path) && $method === 'POST':
        $controller = new AuthController($connection);
        $controller->register();
        break;
    
    // HEALTH CHECK
    case preg_match('~/health~', $path) && $method === 'GET':
        ApiResponse::send(
            ApiResponse::success(['status' => 'online'], 'API online', 200)
        );
        break;
    
    // USUARIOS ROUTES
    case preg_match('~/usuarios(?:/(\d+))?$~', $path, $matches) && $method === 'GET':
        $controller = new UsuarioController($connection, $auth);
        if (isset($matches[1])) {
            $controller->obter($matches[1]);
        } else {
            $controller->listar();
        }
        break;
        
    case preg_match('~/usuarios/(\d+)$~', $path, $matches) && $method === 'PUT':
        $controller = new UsuarioController($connection, $auth);
        $controller->atualizar($matches[1]);
        break;

    case preg_match('~/usuarios/(\d+)$~', $path, $matches) && $method === 'DELETE':
        $controller = new UsuarioController($connection, $auth);
        $controller->deletar($matches[1]);
        break;
    
    // OBJETOS ROUTES
    case preg_match('~/objetos(?:/(\d+))?$~', $path, $matches) && $method === 'GET':
        $controller = new ObjetoController($connection, $auth);
        if (isset($matches[1])) {
            $controller->obter($matches[1]);
        } else {
            $controller->listar();
        }
        break;
        
    case preg_match('~/objetos$~', $path) && $method === 'POST':
        $controller = new ObjetoController($connection, $auth);
        $controller->criar();
        break;

    case preg_match('~/objetos/(\d+)$~', $path, $matches) && $method === 'DELETE':
        $controller = new ObjetoController($connection, $auth);
        $controller->deletar($matches[1]);
        break;
    
    // EMPRESTIMOS ROUTES
    case preg_match('~/emprestimos$~', $path) && $method === 'GET':
        $controller = new EmprestimoController($connection, $auth);
        $controller->listar();
        break;
        
    case preg_match('~/emprestimos$~', $path) && $method === 'POST':
        $controller = new EmprestimoController($connection, $auth);
        $controller->criar();
        break;
        
    case preg_match('~/emprestimos/(\d+)/devolver~', $path, $matches) && $method === 'PUT':
        $controller = new EmprestimoController($connection, $auth);
        $controller->devolver($matches[1]);
        break;
        
    default:
        http_response_code(404);
        ApiResponse::send(
            ApiResponse::error('Rota não encontrada', 404)
        );
}

$connection->close();
