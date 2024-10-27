<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use phpcent\Client as CentrifugoClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class EventServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            RedisEventPublisher::class => DI\autowire(),
            CentrifugoEventPublisher::class => DI\autowire(),
            EventSubscriberInterface::class => DI\add([
                DI\get(RedisEventPublisher::class),
                DI\get(CentrifugoEventPublisher::class)
            ]),
            HealthCheckInterface::class => DI\add([
                DI\get(RedisHealthCheck::class),
                DI\get(CentrifugoHealthCheck::class)
            ]),
            Redis::class => DI\factory(function (ContainerInterface $container) {
                /** @var LoggerInterface $logger */
                $logger = $container->get(LoggerInterface::class);
                $host = $container->get('config.redis.host');
                $timeout = 60;
                $attempt = 1;
                $startedAt = time();

                do {
                    try {
                        $redis = new Redis();
                        $redis->connect($host);
                    } catch (Throwable $exception) {
                        $redis = null;
                        $logger->warning($exception->getMessage(), ['host' => $host, 'attempt' => $attempt]);
                        sleep(5);
                        if (time() - $startedAt < $timeout) {
                            $attempt++;
                        } else {
                            throw $exception;
                        }
                    }
                } while ($redis === null);

                $version = $redis->info('server')['redis_version'] ?? null;
                $logger->debug('Connected to redis', ['version' => $version]);

                return $redis;
            }),
            CentrifugoClient::class => DI\factory(function (ContainerInterface $container) {
                /** @var LoggerInterface $logger */
                $logger = $container->get(LoggerInterface::class);
                $url = $container->get('config.centrifugo.apiUrl');
                $key = $container->get('config.centrifugo.apiKey');
                $logger->debug('Connecting to centrifugo', ['url' => $url]);

                return new CentrifugoClient($url, $key);
            })
        ];
    }
}
