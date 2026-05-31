<?php

class ConfiguracaoBancoDados
{
    public static function obterConexao()
    {
        $endereco = getenv('DB_HOST') ?: 'localhost';
        $usuario = getenv('DB_USER') ?: 'locket_app';
        $senha = getenv('DB_PASS') ?: '';
        $nomeBanco = getenv('DB_NAME') ?: 'locket_db';

        try {
            $conexao = new mysqli($endereco, $usuario, $senha, $nomeBanco);

            if ($conexao->connect_error) {
                throw new Exception('Erro na conexao: ' . $conexao->connect_error);
            }

            $conexao->set_charset('utf8mb4');

            return $conexao;
        } catch (Exception $e) {
            error_log('Erro ao conectar ao banco: ' . $e->getMessage());
            throw $e;
        }
    }
}
