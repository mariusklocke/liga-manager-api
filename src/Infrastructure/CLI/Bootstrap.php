<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\ContainerBuilder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

class Bootstrap
{
    /**
     * @return Application
     */
    public static function bootstrap(): Application
    {
        $container = ContainerBuilder::build();

        $app = new Application();
        $app->setCatchExceptions(true);
        $app->setCommandLoader($container->get(CommandLoaderInterface::class));

        return $app;
    }
}
