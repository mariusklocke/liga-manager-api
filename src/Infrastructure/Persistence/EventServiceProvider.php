<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Retry;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            EventSubscriberInterface::class => DI\add(DI\get(RedisEventPublisher::class)),
            HealthCheckInterface::class => DI\add(DI\get(RedisHealthCheck::class)),
            Redis::class => DI\factory(function (ContainerInterface $container) {
                $host  = $container->get('config.redis.host');
                $retry = new Retry($container->get(LoggerInterface::class), 60, 5);

                return $retry(function () use ($host) {
                    $redis = new Redis();
                    @$redis->connect($host);

                    return $redis;
                });
            })
        ];
    }
}
