<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Container\ContainerInterface;
use Redis;

class RedisHealthCheck implements HealthCheckInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $this->container->get(Redis::class)->ping();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'redis';
    }
}