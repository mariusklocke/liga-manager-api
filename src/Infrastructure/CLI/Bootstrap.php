<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Infrastructure\CommandBusProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Import\L98ImportProvider;
use HexagonalPlayground\Infrastructure\Import\L98ImportService;
use HexagonalPlayground\Infrastructure\LoggerProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Slim\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

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
        $app->setCommandLoader(new FactoryCommandLoader([
            'app:load-fixtures' => function () use ($container) {
                return new LoadFixturesCommand($container['commandBus']);
            },
            'app:create-user' => function () use ($container) {
                return new CreateUserCommand($container['commandBus']);
            },
            'app:reset-password' => function () use ($container) {
                return new ResetPasswordCommand(
                    $container[MailerInterface::class],
                    $container[TemplateRenderer::class]
                );
            },
            'app:list-events' => function () use ($container) {
                return new ListEventsCommand($container[EventStoreInterface::class]);
            },
            'app:import-season' => function () use ($container) {
                return new L98ImportCommand(
                    $container[OrmTransactionWrapperInterface::class],
                    $container[L98ImportService::class]
                );
            }
        ]));
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

        return $container;
    }
}