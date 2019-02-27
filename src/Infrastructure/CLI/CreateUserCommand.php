<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CreateUserCommand as CreateUserApplicationCommand;
use HexagonalPlayground\Domain\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:create-user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $styledIo = $this->getStyledIO($input, $output);

        $command = new CreateUserApplicationCommand(
            null,
            $styledIo->ask('Email'),
            $styledIo->ask('Password'),
            $styledIo->ask('First name'),
            $styledIo->ask('Last name'),
            $styledIo->choice('Choose a role', [User::ROLE_ADMIN, User::ROLE_TEAM_MANAGER], User::ROLE_ADMIN),
            []
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));

        $output->writeln('User successfully created. ID: ' . $command->getId());
        return 0;
    }
}