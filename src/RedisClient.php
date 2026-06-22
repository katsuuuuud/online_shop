<?php
require_once __DIR__ . '/config.php';

class RedisClient
{
    private static ?RedisClient $instance = null;
    private Redis $client;

    private function __construct()
    {
        $this->client = new Redis();
        $this->client->connect(REDIS_HOST, (int)REDIS_PORT, 2.0);
    }

    public static function getInstance(): RedisClient
    {
        if (self::$instance === null) {
            self::$instance = new RedisClient();
        }
        return self::$instance;
    }

    public function getRedis(): Redis
    {
        return $this->client;
    }

    public function get(string $key): ?string
    {
        $value = $this->client->get($key);
        return $value === false ? null : $value;
    }

    public function setex(string $key, int $ttl, string $value): bool
    {
        return $this->client->setex($key, $ttl, $value);
    }

    public function del(string $key): int
    {
        return $this->client->del($key);
    }

    public function exists(string $key): bool
    {
        return $this->client->exists($key) > 0;
    }

    public function ping(): bool
    {
        return $this->client->ping() === '+PONG';
    }
}