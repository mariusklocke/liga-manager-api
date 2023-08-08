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
use HexagonalPlayground\Infrastructure\Config;
use Iterator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container): Application
    {
        $app = new Application();
        $app->setCatchExceptions(true);

        foreach ($this->getOwnCommands($container) as $command) {
            $app->add($command);
        }

        $migrationsDependencyFactory = $this->getDoctrineMigrationsDependencyFactory(
            $this->getDoctrineMigrationConfig(),
            $this->getDoctrineMigrationsConnectionLoader($container)
        );

        foreach ($this->getDoctrineMigrationsCommands($migrationsDependencyFactory) as $command) {
            $app->add($command);
        }

        $entityManagerProvider = $this->getDoctrineOrmEntityManagerProvider($container);

        foreach ($this->getDoctrineOrmCommands($entityManagerProvider) as $command) {
            $app->add($command);
        }

        return $app;
    }

    /**
     * @param ContainerInterface $container
     * @return Iterator
     */
    private function getOwnCommands(ContainerInterface $container): Iterator
    {
        yield new PrintGraphQlSchemaCommand($container);
        yield new MaintenanceModeCommand($container);
        yield new SendTestMailCommand($container);
        yield new SetupEnvCommand($container);
        yield new HealthCommand($container);
        yield new CreateUserCommand($container);
        yield new DeleteUserCommand($container);
        yield new ListUserCommand($container);
        yield new L98ImportCommand($container);
        yield new LoadDemoDataCommand($container);
        yield new WipeDbCommand($container);
    }

    /**
     * @return Configuration
     */
    private function getDoctrineMigrationConfig(): Configuration
    {
        $migrationsConfig = new Configuration();
        $migrationsConfig->addMigrationsDirectory(
            'Migrations',
            Config::getInstance()->appHome . '/migrations'
        );

        return $migrationsConfig;
    }

    /**
     * @param ContainerInterface $container
     * @return ConnectionLoader
     */
    private function getDoctrineMigrationsConnectionLoader(ContainerInterface $container): ConnectionLoader
    {
        return new class($container) implements ConnectionLoader {
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
    }

    /**
     * @param Configuration $config
     * @param ConnectionLoader $connectionLoader
     * @return DependencyFactory
     */
    private function getDoctrineMigrationsDependencyFactory(Configuration $config, ConnectionLoader $connectionLoader): DependencyFactory
    {
        return DependencyFactory::fromConnection(new ExistingConfiguration($config), $connectionLoader);
    }

    /**
     * @param DependencyFactory $dependencyFactory
     * @return Iterator
     */
    private function getDoctrineMigrationsCommands(DependencyFactory $dependencyFactory): Iterator
    {
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
     * @return EntityManagerProvider
     */
    private function getDoctrineOrmEntityManagerProvider(ContainerInterface $container): EntityManagerProvider
    {
        return new class($container) implements EntityManagerProvider {
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
    }

    /**
     * @param EntityManagerProvider $emProvider
     * @return Iterator
     */
    private function getDoctrineOrmCommands(EntityManagerProvider $emProvider): Iterator
    {
        yield new CreateCommand($emProvider);
        yield new UpdateCommand($emProvider);
        yield new DropCommand($emProvider);
        yield new GenerateProxiesCommand($emProvider);
        yield new ValidateSchemaCommand($emProvider);
        yield new InfoCommand($emProvider);
    }
}
