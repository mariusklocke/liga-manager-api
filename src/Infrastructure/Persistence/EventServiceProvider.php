<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use DI;
use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Application\EventStoreSubscriber;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineEventStore;
use Psr\Container\ContainerInterface;
use Redis;
use RuntimeException;

class EventServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            HandlerResolver::class => DI\decorate(function (HandlerResolver $resolver, ContainerInterface $container) {
                Publisher::getInstance()->addSubscriber($container->get(EventStoreSubscriber::class));
                Publisher::getInstance()->addSubscriber($container->get(RedisEventPublisher::class));

                return $resolver;
            }),

            HealthCheckInterface::class => DI\add(DI\get(RedisHealthCheck::class)),

            Redis::class => DI\factory(function() {
                $redis = new Redis();
                if (false === $redis->connect(Environment::get('REDIS_HOST'))) {
                    throw new RuntimeException('Could not connect to redis');
                }

                return $redis;
            }),

            EventStoreInterface::class => DI\get(DoctrineEventStore::class),

            DoctrineEventStore::class => DI\autowire(),

            EventStoreSubscriber::class => DI\autowire(),

            RedisEventPublisher::class => DI\autowire()
        ];
    }
}