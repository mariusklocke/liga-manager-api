<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\StreamFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            StreamFactoryInterface::class => DI\get(Psr17Factory::class),
            TeamLogoRepository::class => DI\autowire()
        ];
    }
}
