<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Persistence\QueryLogger;

class ReadRepositoryProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            DbalGateway::class => DI\autowire(),

            ReadDbGatewayInterface::class => DI\get(DbalGateway::class),

            QueryLogger::class => DI\autowire(),

            __NAMESPACE__ . '\*Repository' => DI\autowire()
        ];
    }
}
