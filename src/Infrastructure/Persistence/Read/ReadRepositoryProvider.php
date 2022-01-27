<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ReadRepositoryProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            DbalGateway::class => DI\autowire(),

            ReadDbGatewayInterface::class => DI\get(DbalGateway::class),

            __NAMESPACE__ . '\*Repository' => DI\autowire()
        ];
    }
}
