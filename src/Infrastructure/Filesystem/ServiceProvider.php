<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            FilesystemService::class => DI\autowire(),
            TeamLogoRepository::class => DI\create()->constructor(
                DI\get(FilesystemService::class),
                DI\get('config.api.appLogosPath'),
                DI\get('config.api.appLogosPublicPath')
            )
        ];
    }
}
