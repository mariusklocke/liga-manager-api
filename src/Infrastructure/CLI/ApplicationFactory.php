<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ConnectionLoader;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Iterator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container): Application
    {
        $app = new Application();
        $app->setCatchExceptions(true);

        // Own commands
        $app->addCommands([
            new PrintGraphQlSchemaCommand($container),
            new MaintenanceModeCommand($container),
            new SendTestMailCommand($container),
            new SetupEnvCommand($container),
            new HealthCommand($container),
            new CreateUserCommand($container),
            new DeleteUserCommand($container),
            new ListUserCommand($container),
            new L98ImportCommand($container),
            new LoadDemoDataCommand($container),
            new WipeDbCommand($container)
        ]);

        foreach ($this->getDoctrineMigrationsCommands($container) as $command) {
            $app->add($command);
        }

        foreach ($this->getDoctrineOrmCommands($container) as $command) {
            $app->add($command);
        }

        return $app;
    }

    /**
     * @param ContainerInterface $container
     * @return Iterator
     */
    private function getDoctrineMigrationsCommands(ContainerInterface $container): Iterator
    {
        $migrationsConfig = new Configuration();
        $migrationsConfig->addMigrationsDirectory(
            'Migrations',
            getenv('APP_HOME') . '/migrations'
        );

        $connectionLoader = new class($container) implements ConnectionLoader {
            private ContainerInterface $container;

            public function __construct(ContainerInterface $container)
            {
                $this->container = $container;
            }

            public function getConnection(?string $name = null): Connection
            {
                return $this->container->get(Connection::class);
            }
        };

        $dependencyFactory = DependencyFactory::fromConnection(
            new ExistingConfiguration($migrationsConfig),
            $connectionLoader
        );

        yield new CurrentCommand($dependencyFactory);
        yield new DumpSchemaCommand($dependencyFactory);
        yield new ExecuteCommand($dependencyFactory);
        yield new GenerateCommand($dependencyFactory);
        yield new LatestCommand($dependencyFactory);
        yield new MigrateCommand($dependencyFactory);
        yield new RollupCommand($dependencyFactory);
        yield new StatusCommand($dependencyFactory);
        yield new VersionCommand($dependencyFactory);
        yield new UpToDateCommand($dependencyFactory);
        yield new SyncMetadataCommand($dependencyFactory);
        yield new ListCommand($dependencyFactory);
    }

    /**
     * @param ContainerInterface $container
     * @return Iterator
     */
    private function getDoctrineOrmCommands(ContainerInterface $container): Iterator
    {
        $emProvider = new class($container) implements EntityManagerProvider {
            private ContainerInterface $container;

            public function __construct(ContainerInterface $container)
            {
                $this->container = $container;
            }

            public function getDefaultManager(): EntityManagerInterface
            {
                return $this->container->get(EntityManagerInterface::class);
            }

            public function getManager(string $name): EntityManagerInterface
            {
                return $this->container->get(EntityManagerInterface::class);
            }
        };

        yield new CreateCommand($emProvider);
        yield new UpdateCommand($emProvider);
        yield new DropCommand($emProvider);
        yield new GenerateProxiesCommand($emProvider);
        yield new ValidateSchemaCommand($emProvider);
        yield new InfoCommand($emProvider);
    }
}
