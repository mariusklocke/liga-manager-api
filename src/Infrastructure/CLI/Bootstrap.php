<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\ContainerBuilder;
use Symfony\Component\Console\Application;

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
        $app->addCommands([
            new CreateUserCommand($container),
            new DebugGqlSchemaCommand($container),
            new L98ImportCommand($container),
            new LoadFixturesCommand($container),
            new SendTestMailCommand($container),
            new SetupCommand($container)
        ]);

        return $app;
    }
}
