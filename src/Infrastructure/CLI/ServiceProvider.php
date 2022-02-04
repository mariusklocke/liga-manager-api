<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use DI;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
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
use Exception;
use HexagonalPlayground\Application\Import\TeamMapperInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Iterator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Application::class => DI\factory(function (ContainerInterface $container) {
                $app = new Application();
                $app->setCatchExceptions(true);
                $app->addCommands([
                    new DebugGqlSchemaCommand($container),
                    new MaintenanceModeCommand($container),
                    new SendTestMailCommand($container),
                    new SetupEnvCommand($container),
                    new HealthCommand($container)
                ]);

                try {
                    /** @var Connection $dbConnection */
                    $dbConnection = $container->get(Connection::class);
                    $dbConnection->connect();
                } catch (Exception $e) {
                    $dbConnection = null;
                }

                if ($dbConnection !== null) {
                    // Own commands
                    $app->addCommands([
                        new CreateUserCommand($container),
                        new L98ImportCommand($container),
                        new LoadDemoDataCommand($container),
                        new WipeDbCommand($container)
                    ]);

                    foreach ($this->getDoctrineMigrationsCommands($dbConnection) as $command) {
                        $app->add($command);
                    }
                }

                return $app;
            }),
            TeamMapperInterface::class => DI\get(TeamMapper::class)
        ];
    }

    /**
     * @param Connection $dbConnection
     * @return Iterator
     */
    private function getDoctrineMigrationsCommands(Connection $dbConnection): Iterator
    {
        $migrationsConfig = new Configuration();
        $migrationsConfig->addMigrationsDirectory(
            'Migrations',
            getenv('APP_HOME') . '/migrations'
        );

        $dependencyFactory = DependencyFactory::fromConnection(
            new ExistingConfiguration($migrationsConfig),
            new ExistingConnection($dbConnection)
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
}
