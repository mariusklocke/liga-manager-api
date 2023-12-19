<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LoggerProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            LoggerInterface::class => DI\factory(function (ContainerInterface $container) {
                /** @var Config $config */
                $config = $container->get(Config::class);
                /** @var FilesystemService $filesystem */
                $filesystem = $container->get(FilesystemService::class);

                return new Logger(
                    $filesystem->openFile($config->logPath, 'w'),
                    $config->logLevel
                );
            })
        ];
    }
}
