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
                    SetupCommand::NAME => SetupCommand::class,
                    DebugGqlSchemaCommand::NAME => DebugGqlSchemaCommand::class,
                    LoadFixturesCommand::NAME => LoadFixturesCommand::class,
                    CreateUserCommand::NAME => CreateUserCommand::class,
                    L98ImportCommand::NAME => L98ImportCommand::class,
                    SendTestMailCommand::NAME => SendTestMailCommand::class
                ]),

            TeamMapperInterface::class => DI\get(TeamMapper::class),
            TeamMapper::class => DI\autowire()
        ];
    }
}