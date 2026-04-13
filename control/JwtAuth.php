<?php

class JwtAuth
{
    private static $secret = '';
    private static $algorithm = 'HS256';
    private static $expirationTime = 3600; // 1 hora
    
    public static function initialize()
    {
        self::$secret = getenv('JWT_SECRET') ?: 'sua_chave_secreta_muito_segura';
        self::$expirationTime = (int) (getenv('JWT_EXPIRATION') ?: 3600);
    }
    
    public static function generateToken($payload)
    {
        self::initialize();
        
        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expirationTime;
        
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            self::$secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    public static function verifyToken($token)
    {
        self::initialize();
        
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Token inválido');
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            self::$secret,
            true
        );
        $signatureVerify = self::base64UrlEncode($signature);
        
        if ($signatureEncoded !== $signatureVerify) {
            throw new Exception('Token inválido ou expirado');
        }
        
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        if ($payload['exp'] < time()) {
            throw new Exception('Token expirado');
        }
        
        return $payload;
    }
    
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}
