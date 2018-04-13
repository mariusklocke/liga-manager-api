<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use HexagonalPlayground\Application\FixtureLoader;
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
        // Add own command
        $app->add(new ImportMatchesCommand($container['batchCommandBus']));
        $app->add(new LoadFixturesCommand($container[FixtureLoader::class]));
        $app->add(new CreateUserCommand($container['commandBus']));
        return $app;
    }
}