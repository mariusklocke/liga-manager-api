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
            Definition::class => [
                DI\create(Definition::class)->constructor('php_database_queries', 'counter', 'Executed database queries', ['action']),
                DI\create(Definition::class)->constructor('php_memory_usage', 'gauge', 'Used memory in bytes'),
                DI\create(Definition::class)->constructor('php_memory_peak_usage', 'gauge', 'Peak used memory in bytes'),
                DI\create(Definition::class)->constructor('php_requests', 'counter', 'HTTP requests', ['auth', 'status']),
            ],
            StoreInterface::class => DI\factory(function (ContainerInterface $container): StoreInterface {
                $definitions = $container->get(Definition::class);
                $exportUrl = getenv('METRICS_EXPORT_URL');
                $publishUrl = getenv('METRICS_PUBLISH_URL');

                if ($exportUrl && $publishUrl) {
                    return new RoadRunnerStore($definitions, $exportUrl, $publishUrl);
                }

                return new ApcuStore($definitions);
            }),
            EventSubscriberInterface::class => DI\add(DI\get(EventSubscriber::class))
        ];
    }
}
