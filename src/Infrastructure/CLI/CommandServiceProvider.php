<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use GraphQL\Type\Schema;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Import\Importer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

class CommandServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container[CommandLoaderInterface::class] = function () use ($container) {
            return new FactoryCommandLoader([
                'app:load-fixtures' => function () use ($container) {
                    return new LoadFixturesCommand($container['commandBus']);
                },
                'app:create-user' => function () use ($container) {
                    return new CreateUserCommand($container['commandBus']);
                },
                'app:import-season' => function () use ($container) {
                    return new L98ImportCommand(
                        $container[OrmTransactionWrapperInterface::class],
                        $container[Importer::class]
                    );
                },
                'app:send-test-mail' => function () use ($container) {
                    return new SendTestMailCommand($container[MailerInterface::class]);
                },
                'app:debug-gql-schema' => function () use ($container) {
                    return new DebugGqlSchemaCommand($container[Schema::class]);
                },
                'app:setup' => function () use ($container) {
                    return new SetupCommand();
                }
            ]);
        };
    }
}