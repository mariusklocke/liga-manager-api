<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\ContainerBuilder;
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
        $this->container = ContainerBuilder::build(new ServiceProvider());

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

        foreach ($this->getCommands() as $command) {
            $this->add($command);
        }
    }

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
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
    private function getCommands(): Iterator
    {
        // Own commands
        yield new SetupEnvCommand($this->container, $this->authContext);
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
        yield new CheckHealthCommand($this->container, $this->authContext);
        yield new MigrateDbCommand($this->container, $this->authContext);
        yield new ValidateConfigCommand($this->container, $this->authContext);
        yield new SendMailCommand($this->container, $this->authContext);
        yield new ShowConfigCommand($this->container, $this->authContext);
        yield new InspectContainerCommand($this->container, $this->authContext);
        yield new ListVersionsCommand($this->container, $this->authContext);
        yield new ExecuteCommandCommand($this->container, $this->authContext);

        // Doctrine ORM commands
        $emProvider = $this->container->get(EntityManagerProvider::class);
        yield new CreateCommand($emProvider);
        yield new UpdateCommand($emProvider);
        yield new DropCommand($emProvider);
        yield new GenerateProxiesCommand($emProvider);
        yield new ValidateSchemaCommand($emProvider);
        yield new InfoCommand($emProvider);
    }
}
