<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use DI;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ConnectionLoader;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use HexagonalPlayground\Application\Import\TeamMapperInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Configuration::class => DI\factory(function (ContainerInterface $container) {
                /** @var FilesystemService $filesystem */
                $filesystem = $container->get(FilesystemService::class);

                $migrationsConfig = new Configuration();
                $migrationsConfig->addMigrationsDirectory(
                    'Migrations',
                    $filesystem->joinPaths([$container->get('app.home'), 'migrations'])
                );

                return $migrationsConfig;
            }),

            ConnectionLoader::class => DI\factory(function (ContainerInterface $container) {
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
            }),

            DependencyFactory::class => DI\factory(function (ContainerInterface $container) {
                $config = $container->get(Configuration::class);
                $connectionLoader = $container->get(ConnectionLoader::class);

                return DependencyFactory::fromConnection(new ExistingConfiguration($config), $connectionLoader);
            }),

            EntityManagerProvider::class => DI\factory(function (ContainerInterface $container) {
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
            }),

            InputInterface::class => DI\get(ArgvInput::class),
            LoggerInterface::class => DI\factory(function (ContainerInterface $container) {
                return new ConsoleLogger($container->get(OutputInterface::class));
            }),
            OutputInterface::class => DI\get(ConsoleOutput::class),
            TeamMapperInterface::class => DI\get(TeamMapper::class),
            UploadedFileFactoryInterface::class => DI\get(Psr17Factory::class)
        ];
    }
}
