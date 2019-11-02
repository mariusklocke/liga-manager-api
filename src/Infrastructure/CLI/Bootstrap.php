<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\ServiceProvider as CommandBusProvider;
use HexagonalPlayground\Application\Handler\ServiceProvider as CommandHandlerProvider;
use HexagonalPlayground\Application\Import\L98ImportProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\SchemaProvider;
use HexagonalPlayground\Infrastructure\CLI\ServiceProvider as CliServiceProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\LoggerProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use Pimple\ServiceProviderInterface;
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
        $app->setCatchExceptions(true);
        $app->setCommandLoader($container[CommandLoaderInterface::class]);
        return $app;
    }

    /**
     * @return Container
     */
    public static function createContainer(): Container
    {
        $container = new Container();

        foreach (self::getServiceProvider() as $provider) {
            $provider->register($container);
        }

        return $container;
    }

    /**
     * @return ServiceProviderInterface[]
     */
    private static function getServiceProvider(): array
    {
        return [
            new MailServiceProvider(),
            new CommandHandlerProvider(),
            new CommandBusProvider(),
            new EventServiceProvider(),
            new LoggerProvider(),
            new DoctrineServiceProvider(),
            new L98ImportProvider(),
            new CliServiceProvider(),
            new SchemaProvider()
        ];
    }
}
