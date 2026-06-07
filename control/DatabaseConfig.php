<?php

class DatabaseConfig
{
    private static function carregarEnv(): array
    {
        $caminhoEnv = dirname(__DIR__) . '/.env';
        $vars = [];

        if (is_file($caminhoEnv)) {
            $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($linhas as $linha) {
                $linha = trim($linha);
                if ($linha === '' || str_starts_with($linha, '#')) {
                    continue;
                }

                [$chave, $valor] = array_pad(explode('=', $linha, 2), 2, '');
                $vars[trim($chave)] = trim(trim($valor), "'\"");
            }
        }

        return $vars;
    }

    public static function getConnection()
    {
        $env = self::carregarEnv();

        $host = getenv('DB_HOST') ?: $env['DB_HOST'] ?? $env['BD_HOST'] ?? 'localhost';
        $user = getenv('DB_USER') ?: $env['DB_USER'] ?? $env['BD_USUARIO'] ?? 'locket_app';
        $password = getenv('DB_PASS') ?: $env['DB_PASS'] ?? $env['BD_SENHA'] ?? '';
        $database = getenv('DB_NAME') ?: $env['DB_NAME'] ?? $env['BD_NOME'] ?? 'locket_db';

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
