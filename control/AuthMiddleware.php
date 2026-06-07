<?php

class AuthMiddleware
{
    private static $publicRoutes = [
        'POST' => ['/auth/login', '/auth/register'],
        'GET' => ['/health']
    ];
    
    public static function authenticate()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = preg_replace('#^(.*/)?index\.php#', '', $path);
        $path = rtrim($path, '/') ?: '/';

        if (self::isPublicRoute($method, $path)) {
            return null;
        }
        
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (empty($authHeader)) {
            ApiResponse::send(
                ApiResponse::error('Token não fornecido', 401)
            );
        }
        
        if (! preg_match('/^Bearer (.+)$/', $authHeader, $matches)) {
            ApiResponse::send(
                ApiResponse::error('Formato de token inválido', 401)
            );
        }
        
        $token = $matches[1];
        
        try {
            $payload = JwtAuth::verifyToken($token);
            return $payload;
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 401)
            );
        }
    }
    
    private static function isPublicRoute($method, $path)
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        
        if (isset(self::$publicRoutes[$method])) {
            foreach (self::$publicRoutes[$method] as $route) {
                $routePattern = preg_quote($route, '/');
                $routePattern = str_replace('\\*', '[^/]+', $routePattern);
                $routePattern = '/^' . $routePattern . '$/';
                
                if (preg_match($routePattern, $normalizedPath)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
