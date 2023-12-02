<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\ServiceProviderInterface;
use DI;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            UploadAction::class => DI\autowire()
        ];
    }
}
