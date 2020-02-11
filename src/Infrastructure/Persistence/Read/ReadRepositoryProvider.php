<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Persistence\QueryLogger;
use mysqli;

class ReadRepositoryProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            mysqli::class => DI\create()
                ->constructor(
                    DI\env('MYSQL_HOST'),
                    DI\env('MYSQL_USER'),
                    DI\env('MYSQL_PASSWORD'),
                    DI\env('MYSQL_DATABASE'),
                    null,
                    null
                )
                ->method('set_charset', 'utf8'),

            HealthCheckInterface::class => DI\add(DI\get(MysqliHealthCheck::class)),

            MysqliReadDbAdapter::class => DI\autowire()
                ->method('setLogger', DI\get(QueryLogger::class)),

            ReadDbAdapterInterface::class => DI\get(MysqliReadDbAdapter::class),

            QueryLogger::class => DI\autowire(),

            __NAMESPACE__ . '\*Repository' => DI\autowire()
        ];
    }
}