<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use HexagonalPlayground\Application\Import\L98ImportProvider;
use HexagonalPlayground\Infrastructure\CommandBusProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\LoggerProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use Slim\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

class Bootstrap
{
    /**
     * @return Application
     */
    public static function bootstrap(): Application
    {
        $container = self::createContainer();

        $app = new Application();
        $app->setHelperSet(ConsoleRunner::createHelperSet($container[EntityManager::class]));
        $app->setCatchExceptions(true);

        // Add Doctrine commands
        ConsoleRunner::addCommands($app);
        // Register loader for lazy-loading own commands
        $app->setCommandLoader($container[CommandLoaderInterface::class]);
        return $app;
    }

    /**
     * @return Container
     */
    private static function createContainer(): Container
    {
        $container = new Container();
        (new MailServiceProvider())->register($container);
        (new CommandBusProvider())->register($container);
        (new EventServiceProvider())->register($container);
        (new LoggerProvider())->register($container);
        (new DoctrineServiceProvider())->register($container);
        (new L98ImportProvider())->register($container);
        (new CommandServiceProvider())->register($container);

        return $container;
    }
}