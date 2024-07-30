<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;
use HexagonalPlayground\Application\ServiceProviderInterface;

use DI;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            'app.metrics.counters' => [
                'requests_total' => 'Amount of total HTTP requests',
                'requests_failed' => 'Amount of failed HTTP requests',
                'requests_auth_jwt' => 'Amount of HTTP requests with JWT authentication',
                'requests_auth_basic' => 'Amount of HTTP requests with Basic authentication',
                'requests_auth_none' => 'Amount of HTTP requests without authentication',
                'database_queries' => 'Amount of database queries executed'
            ],
            'app.metrics.gauges' => [
                'memory_usage' => 'Amount of used memory in bytes',
                'memory_peak_usage' => 'Amount of peak used memory in bytes'
            ],
            ApcuStore::class => DI\create()->constructor(
                DI\get('app.metrics.counters'),
                DI\get('app.metrics.gauges')
            ),
            StoreInterface::class => DI\get(ApcuStore::class),
            EventSubscriberInterface::class => DI\add(DI\get(EventSubscriber::class))
        ];
    }
}
