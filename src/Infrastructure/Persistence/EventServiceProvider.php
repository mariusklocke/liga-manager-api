<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Retry;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            EventDispatcherInterface::class => DI\factory(function (ContainerInterface $container) {
                $eventDispatcher = new EventDispatcher();

                foreach ($container->get(EventSubscriberInterface::class) as $subscriber) {
                    $eventDispatcher->addSubscriber($subscriber);
                }

                return $eventDispatcher;
            }),

            EventSubscriberInterface::class => [
                DI\get(RedisEventPublisher::class)
            ],

            HealthCheckInterface::class => DI\add(DI\get(RedisHealthCheck::class)),

            Redis::class => DI\factory(function (ContainerInterface $container) {
                /** @var Config $config */
                $config = $container->get(Config::class);
                $retry  = new Retry($container->get(LoggerInterface::class), 60, 5);

                return $retry(function () use ($config) {
                    $redis = new Redis();
                    @$redis->connect($config->redisHost);

                    return $redis;
                });
            })
        ];
    }
}
