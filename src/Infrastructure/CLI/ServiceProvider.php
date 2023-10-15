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
use HexagonalPlayground\Infrastructure\Config;
use Psr\Container\ContainerInterface;
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
            Configuration::class => DI\factory(function () {
                $namespace = 'Migrations';
                $path = Config::getInstance()->appHome . '/migrations';

                $migrationsConfig = new Configuration();
                $migrationsConfig->addMigrationsDirectory($namespace, $path);

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
            TeamMapperInterface::class => DI\get(TeamMapper::class)
        ];
    }
}
