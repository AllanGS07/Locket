<?php

class AutenticacaoJwt
{
    private static $chaveSecreta = '';
    private static $algoritmo = 'HS256';
    private static $tempoExpiracaoSegundos = 3600;

    public static function inicializar()
    {
        self::$chaveSecreta = getenv('JWT_SECRET') ?: 'sua_chave_secreta_muito_segura';
        self::$tempoExpiracaoSegundos = (int)(getenv('JWT_EXPIRATION') ?: 3600);
    }

    public static function gerarToken($cargaUtil)
    {
        self::inicializar();

        $cabecalho = [
            'alg' => self::$algoritmo,
            'typ' => 'JWT'
        ];

        $cargaUtil['iat'] = time();
        $cargaUtil['exp'] = time() + self::$tempoExpiracaoSegundos;

        $cabecalhoEncodificado = self::codificarBase64Url(json_encode($cabecalho));
        $cargaUtilEncodificada = self::codificarBase64Url(json_encode($cargaUtil));

        $assinatura = hash_hmac(
            'sha256',
            $cabecalhoEncodificado . '.' . $cargaUtilEncodificada,
            self::$chaveSecreta,
            true
        );
        $assinaturaEncodificada = self::codificarBase64Url($assinatura);

        return $cabecalhoEncodificado . '.' . $cargaUtilEncodificada . '.' . $assinaturaEncodificada;
    }

    public static function verificarToken($token)
    {
        self::inicializar();

        $partes = explode('.', $token);

        if (count($partes) !== 3) {
            throw new Exception('Token invalido');
        }

        list($cabecalhoEncodificado, $cargaUtilEncodificada, $assinaturaEncodificada) = $partes;

        $assinatura = hash_hmac(
            'sha256',
            $cabecalhoEncodificado . '.' . $cargaUtilEncodificada,
            self::$chaveSecreta,
            true
        );
        $assinaturaVerificada = self::codificarBase64Url($assinatura);

        if ($assinaturaEncodificada !== $assinaturaVerificada) {
            throw new Exception('Token invalido ou expirado');
        }

        $cargaUtil = json_decode(self::decodificarBase64Url($cargaUtilEncodificada), true);

        if ($cargaUtil['exp'] < time()) {
            throw new Exception('Token expirado');
        }

        return $cargaUtil;
    }

    private static function codificarBase64Url($dados)
    {
        return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    }

    private static function decodificarBase64Url($dados)
    {
        return base64_decode(strtr($dados, '-_', '+/') . str_repeat('=', 4 - strlen($dados) % 4));
    }
}
