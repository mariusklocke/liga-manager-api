<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;

class L98ImportProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Executor::class => DI\autowire()
        ];
    }
}