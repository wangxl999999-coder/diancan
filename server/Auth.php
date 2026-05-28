<?php
class Auth
{
    private static $secret = 'diancan_secret_key_2024';

    public static function generateToken($data)
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($data));
        $signature = base64_encode(hash_hmac('sha256', "{$header}.{$payload}", self::$secret, true));
        return "{$header}.{$payload}.{$signature}";
    }

    public static function verifyToken($token)
    {
        if (!$token) return false;
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        $signature = base64_encode(hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", self::$secret, true));
        if ($signature !== $parts[2]) return false;
        $payload = json_decode(base64_decode($parts[1]), true);
        if (isset($payload['exp']) && $payload['exp'] < time()) return false;
        return $payload;
    }

    public static function getAdmin()
    {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $token);
        $payload = self::verifyToken($token);
        if (!$payload || ($payload['type'] ?? '') !== 'admin') {
            Response::error('未授权访问', 401);
        }
        return $payload;
    }

    public static function getMember()
    {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $token);
        $payload = self::verifyToken($token);
        if (!$payload || ($payload['type'] ?? '') !== 'member') {
            Response::error('请先登录', 401);
        }
        return $payload;
    }
}
