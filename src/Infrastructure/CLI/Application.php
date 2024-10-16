<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

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
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\ServiceProvider as ApplicationServiceProvider;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\CLI\ServiceProvider as CliServiceProvider;
use HexagonalPlayground\Infrastructure\ContainerBuilder;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Filesystem\ServiceProvider as FilesystemServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use Iterator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    private ContainerInterface $container;
    private AuthContext $authContext;

    public function __construct()
    {
        $serviceProviders = [
            new ApplicationServiceProvider(),
            new DoctrineServiceProvider(),
            new ReadRepositoryProvider(),
            new MailServiceProvider(),
            new EventServiceProvider(),
            new CliServiceProvider(),
            new FilesystemServiceProvider()
        ];

        $this->container = ContainerBuilder::build($serviceProviders);

        $user = new User(
            'cli',
            'cli@example.com',
            '123456',
            'CLI',
            'CLI',
            User::ROLE_ADMIN
        );

        $this->authContext = new AuthContext($user);

        parent::__construct('Liga-Manager', $this->container->get('app.version'));

        foreach ($this->getOwnCommands() as $command) {
            $this->add($command);
        }
        foreach ($this->getDoctrineMigrationsCommands() as $command) {
            $this->add($command);
        }
        foreach ($this->getDoctrineOrmCommands() as $command) {
            $this->add($command);
        }
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $input = $this->container->get(InputInterface::class);
        $output = $this->container->get(OutputInterface::class);

        return parent::run($input, $output);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getAuthContext(): AuthContext
    {
        return $this->authContext;
    }

    /**
     * @return Iterator
     */
    private function getOwnCommands(): Iterator
    {
        yield new SendTestMailCommand($this->container, $this->authContext);
        yield new SetupEnvCommand($this->container, $this->authContext);
        yield new HealthCommand($this->container, $this->authContext);
        yield new CreateUserCommand($this->container, $this->authContext);
        yield new DeleteUserCommand($this->container, $this->authContext);
        yield new ListUserCommand($this->container, $this->authContext);
        yield new L98ImportCommand($this->container, $this->authContext);
        yield new LoadDemoDataCommand($this->container, $this->authContext);
        yield new WipeDbCommand($this->container, $this->authContext);
        yield new BrowseDbCommand($this->container, $this->authContext);
        yield new ExportDbCommand($this->container, $this->authContext);
        yield new ImportDbCommand($this->container, $this->authContext);
        yield new CleanupLogoCommand($this->container, $this->authContext);
        yield new ImportLogoCommand($this->container, $this->authContext);
        yield new CheckMailHealthCommand($this->container, $this->authContext);
    }

    /**
     * @return Iterator
     */
    private function getDoctrineMigrationsCommands(): Iterator
    {
        $dependencyFactory = $this->container->get(DependencyFactory::class);

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
     * @return Iterator
     */
    private function getDoctrineOrmCommands(): Iterator
    {
        $emProvider = $this->container->get(EntityManagerProvider::class);

        yield new CreateCommand($emProvider);
        yield new UpdateCommand($emProvider);
        yield new DropCommand($emProvider);
        yield new GenerateProxiesCommand($emProvider);
        yield new ValidateSchemaCommand($emProvider);
        yield new InfoCommand($emProvider);
    }
}
