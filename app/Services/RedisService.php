<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RedisService
{
    private $redis;
    private const TOKEN_PREFIX = 'token:';
    private const TOKEN_EXPIRY = 86400; // 24 hours

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function storeToken(string $userId, string $token): void
    {
        $key = self::TOKEN_PREFIX . $userId;
        $this->redis->setex($key, self::TOKEN_EXPIRY, $token);
    }

    public function getToken(string $userId): ?string
    {
        $key = self::TOKEN_PREFIX . $userId;
        return $this->redis->get($key);
    }

    public function removeToken(string $userId): void
    {
        $key = self::TOKEN_PREFIX . $userId;
        $this->redis->del($key);
    }

    public function validateToken(string $userId, string $token): bool
    {
        $storedToken = $this->getToken($userId);
        return $storedToken === $token;
    }
}
