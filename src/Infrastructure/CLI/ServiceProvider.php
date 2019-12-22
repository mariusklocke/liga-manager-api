<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use DI;
use HexagonalPlayground\Application\Import\TeamMapperInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            CommandLoaderInterface::class => DI\create(ContainerCommandLoader::class)
                ->constructor(DI\get(ContainerInterface::class), [
                    'app:setup' => SetupCommand::class,
                    'app:debug-gql-schema' => DebugGqlSchemaCommand::class,
                    'app:load-fixtures' => LoadFixturesCommand::class,
                    'app:create-user' => CreateUserCommand::class,
                    'app:import-season' => L98ImportCommand::class,
                    'app:send-test-mail' => SendTestMailCommand::class
                ]),

            TeamMapperInterface::class => DI\get(TeamMapper::class),
            TeamMapper::class => DI\autowire()
        ];
    }
}