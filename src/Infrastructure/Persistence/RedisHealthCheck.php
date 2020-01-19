<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Redis;

class RedisHealthCheck implements HealthCheckInterface
{
    /** @var Redis */
    private $redis;

    /**
     * @param Redis $redis A connected redis instance
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $this->redis->ping();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Redis connection';
    }
}