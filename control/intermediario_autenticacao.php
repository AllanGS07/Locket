<?php

class IntermediarioAutenticacao
{
    private static $rotasPublicas = [
        'POST' => ['/auth/login', '/auth/register'],
        'GET' => ['/health']
    ];

    public static function autenticar()
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (self::rotaEhPublica($metodo, $caminho)) {
            return null;
        }

        $cabecalhoAutorizacao = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($cabecalhoAutorizacao)) {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Token nao fornecido', 401)
            );
        }

        if (!preg_match('/^Bearer (.+)$/', $cabecalhoAutorizacao, $correspondencias)) {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Formato de token invalido', 401)
            );
        }

        $token = $correspondencias[1];

        try {
            $cargaUtil = AutenticacaoJwt::verificarToken($token);
            return $cargaUtil;
        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 401)
            );
        }
    }

    private static function rotaEhPublica($metodo, $caminho)
    {
        $caminhoNormalizado = rtrim($caminho, '/') ?: '/';

        if (isset(self::$rotasPublicas[$metodo])) {
            foreach (self::$rotasPublicas[$metodo] as $rota) {
                $padraoRota = preg_quote($rota, '/');
                $padraoRota = str_replace('\\*', '[^/]+', $padraoRota);
                $padraoRota = '/^' . $padraoRota . '$/';

                if (preg_match($padraoRota, $caminhoNormalizado)) {
                    return true;
                }
            }
        }

        return false;
    }
}
