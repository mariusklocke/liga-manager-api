<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;
use HexagonalPlayground\Application\ServiceProviderInterface;

use DI;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            'app.metrics.counters' => [
                'php_requests_total' => 'Amount of total HTTP requests',
                'php_requests_failed' => 'Amount of failed HTTP requests',
                'php_requests_auth_jwt' => 'Amount of HTTP requests with JWT authentication',
                'php_requests_auth_basic' => 'Amount of HTTP requests with Basic authentication',
                'php_requests_auth_none' => 'Amount of HTTP requests without authentication',
                'php_database_queries' => 'Amount of database queries executed'
            ],
            'app.metrics.gauges' => [
                'php_memory_usage' => 'Amount of used memory in bytes',
                'php_memory_peak_usage' => 'Amount of peak used memory in bytes'
            ],
            ApcuStore::class => DI\create()->constructor(
                DI\get('app.metrics.counters'),
                DI\get('app.metrics.gauges')
            ),
            RoadRunnerStore::class => DI\create()->constructor(
                DI\get('app.metrics.counters'),
                DI\get('app.metrics.gauges')
            ),
            StoreInterface::class => DI\factory(function (ContainerInterface $container): StoreInterface {
                $exportUrl = getenv('METRICS_EXPORT_URL');
                $publishUrl = getenv('METRICS_PUBLISH_URL');

                if ($exportUrl && $publishUrl) {
                    return new RoadRunnerStore(
                        $container->get('app.metrics.counters'),
                        $container->get('app.metrics.gauges'),
                        $exportUrl,
                        $publishUrl
                    );
                }

                return new ApcuStore(
                    $container->get('app.metrics.counters'),
                    $container->get('app.metrics.gauges')
                );
            }),
            EventSubscriberInterface::class => DI\add(DI\get(EventSubscriber::class))
        ];
    }
}
