<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use DI;
use HexagonalPlayground\Application\Import\TeamMapperInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineHealthCheck;
use HexagonalPlayground\Infrastructure\Persistence\Read\MysqliHealthCheck;
use HexagonalPlayground\Infrastructure\Persistence\RedisHealthCheck;
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
                    SendTestMailCommand::NAME => SendTestMailCommand::class,
                    HealthCommand::NAME => HealthCommand::class
                ]),

            HealthCommand::class => DI\create()->constructor([
                DI\get(RedisHealthCheck::class),
                DI\get(MysqliHealthCheck::class),
                DI\get(DoctrineHealthCheck::class)
            ]),

            TeamMapperInterface::class => DI\get(TeamMapper::class),
            TeamMapper::class => DI\autowire()
        ];
    }
}