<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Application\EventStoreSubscriber;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineEventStore;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Redis;
use RuntimeException;

class EventServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container->extend(HandlerResolver::class, function($handlerResolver) use ($container) {
            Publisher::getInstance()->addSubscriber(
                new EventStoreSubscriber($container[EventStoreInterface::class])
            );
            Publisher::getInstance()->addSubscriber(
                new RedisEventPublisher($container[Redis::class])
            );

            return $handlerResolver;
        });
        $container[Redis::class] = function () {
            $redis = new Redis();
            if (false === $redis->connect(Environment::get('REDIS_HOST'))) {
                throw new RuntimeException('Could not connect to redis');
            }

            return $redis;
        };
        $container[EventStoreInterface::class] = function () use ($container) {
            return new DoctrineEventStore($container[EntityManager::class]);
        };
    }
}