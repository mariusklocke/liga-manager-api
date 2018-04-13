<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Console\Application;

class Bootstrap
{
    /**
     * @return Application
     */
    public static function bootstrap()
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $app = new Application();
        $app->setHelperSet(ConsoleRunner::createHelperSet($container['doctrine.entityManager']));
        $app->setCatchExceptions(true);

        // Add Doctrine commands
        ConsoleRunner::addCommands($app);
        // Register loader for lazy-loading own commands
        $app->setCommandLoader(new ContainerCommandLoader($container));
        return $app;
    }
}