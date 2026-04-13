<?php

class DatabaseConfig
{
    public static function getConnection()
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'locket_app';
        $password = getenv('DB_PASS') ?: '';
        $database = getenv('DB_NAME') ?: 'locket_db';
        
        try {
            $connection = new mysqli($host, $user, $password, $database);
            
            if ($connection->connect_error) {
                throw new Exception('Erro na conexão: ' . $connection->connect_error);
            }
            
            $connection->set_charset('utf8mb4');
            
            return $connection;
        } catch (Exception $e) {
            error_log('Erro ao conectar ao banco: ' . $e->getMessage());
            throw $e;
        }
    }
}
