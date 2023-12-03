<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\ServiceProviderInterface;
use DI;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            UploadAction::class => DI\factory(function (ContainerInterface $container) {
                return new UploadAction(
                    Config::getInstance()->appLogosPath,
                    '/logos',
                    $container->get(LoggerInterface::class)
                );
            })
        ];
    }
}
