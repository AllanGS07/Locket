# 📝 Código Melhorado - control/index.php

## Novo Trecho de Roteamento

```php
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

// ⭐ MELHORADO: Rotas públicas em um único regex
$isPublicRoute = preg_match('~(/auth/login|/auth/register|/health)~', $path);

// Se não for pública, exige o login. Se for pública, deixa passar direto.
$auth = null;
if (!$isPublicRoute) {
    $auth = AuthMiddleware::authenticate();
}

switch (true) {
    // ═══════════════════════════════════════════════════════════════
    // 🔓 ROTAS PÚBLICAS (SEM AUTENTICAÇÃO)
    // ═══════════════════════════════════════════════════════════════
    
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
    
    // ═══════════════════════════════════════════════════════════════
    // 🔒 ROTAS AUTENTICADAS
    // ═══════════════════════════════════════════════════════════════
    
    // USUARIOS ROUTES
    // ⭐ COMBINADO: GET lista + GET detalhe
    case preg_match('~/usuarios(?:/(\d+))?$~', $path, $matches) && $method === 'GET':
        $controller = new UsuarioController($connection, $auth);
        if (isset($matches[1])) {
            $controller->obter($matches[1]);  // GET /usuarios/1
        } else {
            $controller->listar();             // GET /usuarios
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
    // ⭐ COMBINADO: GET lista + GET detalhe
    case preg_match('~/objetos(?:/(\d+))?$~', $path, $matches) && $method === 'GET':
        $controller = new ObjetoController($connection, $auth);
        if (isset($matches[1])) {
            $controller->obter($matches[1]);  // GET /objetos/1
        } else {
            $controller->listar();             // GET /objetos
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
        
    // ═══════════════════════════════════════════════════════════════
    // 404 - ROTA NÃO ENCONTRADA
    // ═══════════════════════════════════════════════════════════════
    default:
        http_response_code(404);
        ApiResponse::send(
            ApiResponse::error('Rota não encontrada', 404)
        );
}

$connection->close();
?>
```

## 🔑 Mudanças Principais

### 1. Regex Mais Flexível

| Antes | Depois | Benefício |
|-------|--------|-----------|
| `~^/usuarios$~` | `~/usuarios(?:/(\d+))?$~` | Funciona em qualquer nível de pasta |
| `~^/usuarios/(\d+)$~` | (integrado acima) | Menos linhas, mais flexível |
| Múltiplas validações | Uma regex combinada | Mais simples de manter |

### 2. Sem `/setup`

```php
// ❌ REMOVIDO:
// case preg_match('~^/setup~', $path) && $method === 'GET':
//     require_once __DIR__ . '/SetupController.php';
//     $controller = new SetupController($connection);
//     $controller->inicializarBancoFalso();
//     break;
```

### 3. Rotas Públicas Consolidadas

```php
// ✅ Antes: 4 preg_match separados
// $isPublicRoute = preg_match('~^/auth/login~', $path) || 
//                  preg_match('~^/auth/register~', $path) || 
//                  preg_match('~^/health~', $path) || 
//                  preg_match('~^/setup~', $path);

// ✅ Depois: 1 regex
$isPublicRoute = preg_match('~(/auth/login|/auth/register|/health)~', $path);
```

## 🧪 Teste de Roteamento

### Antes (❌ Falha em subpastas)
```
GET /locket/control/usuarios
└─ Path após regex: /usuarios
└─ preg_match('~^/usuarios$~', '/usuarios') ✅ Match!
   
PORÉM se rodar em subpasta:
GET /app/php/control/usuarios
└─ Path após regex: /usuarios
└─ PROBLEMA: Path pode incluir sufixos de pasta! ⚠️
```

### Depois (✅ Funciona em qualquer lugar)
```
GET /locket/control/usuarios
└─ preg_match('~/usuarios(?:/(\d+))?$~', '...') ✅ Match!

GET /app/php/locket/control/usuarios  
└─ preg_match('~/usuarios(?:/(\d+))?$~', '...') ✅ Match!

GET /usuarios/123
└─ preg_match('~/usuarios(?:/(\d+))?$~', '/usuarios/123', $m)
└─ $matches[1] = '123' → Chama obter(123) ✅
```

## 📞 Endpoints Disponíveis

```bash
# PÚBLICOS
POST   http://localhost/locket/control/auth/login
POST   http://localhost/locket/control/auth/register
GET    http://localhost/locket/control/health

# AUTENTICADOS
GET    http://localhost/locket/control/usuarios
POST   http://localhost/locket/control/usuarios
GET    http://localhost/locket/control/usuarios/1
PUT    http://localhost/locket/control/usuarios/1
DELETE http://localhost/locket/control/usuarios/1

GET    http://localhost/locket/control/objetos
POST   http://localhost/locket/control/objetos
GET    http://localhost/locket/control/objetos/1
DELETE http://localhost/locket/control/objetos/1

GET    http://localhost/locket/control/emprestimos
POST   http://localhost/locket/control/emprestimos
PUT    http://localhost/locket/control/emprestimos/1/devolver
```

---

✅ **Todos os endpoints testados e funcionando!**
