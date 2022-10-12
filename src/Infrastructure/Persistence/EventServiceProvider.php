<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Redis;
use RedisException;
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
                $redis = new Redis();

                $attempt = 1;
                do {
                    $exception = null;

                    try {
                        @$redis->connect(Environment::get('REDIS_HOST'));
                    } catch (RedisException $e) {
                        /** @var LoggerInterface $logger */
                        $logger = $container->get(LoggerInterface::class);
                        $logger->warning('Failed to connect to redis.', [
                            'attempt' => $attempt
                        ]);
                        $exception = $e;
                        sleep(5);
                    }

                    $attempt++;
                } while ($exception !== null && $attempt < 10);

                return $redis;
            })
        ];
    }
}
