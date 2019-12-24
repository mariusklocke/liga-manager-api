<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            __NAMESPACE__ . '\*Handler' => DI\autowire()
        ];
    }
}
