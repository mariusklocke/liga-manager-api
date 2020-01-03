<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CreateUserCommand as CreateUserApplicationCommand;
use HexagonalPlayground\Domain\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    public const NAME = 'app:create-user';

    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('email',null, InputOption::VALUE_REQUIRED);
        $this->addOption('password', null, InputOption::VALUE_REQUIRED);
        $this->addOption('first-name', null, InputOption::VALUE_REQUIRED);
        $this->addOption('last-name', null, InputOption::VALUE_REQUIRED);
        $this->addOption('role', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $styledIo = $this->getStyledIO($input, $output);

        $email = $input->getOption('email');
        if (!$email && $input->isInteractive()) {
            $email = $styledIo->ask('Email');
        }

        $password = $input->getOption('password');
        if (!$password && $input->isInteractive()) {
            $password = $styledIo->ask('Password');
        }

        $firstName = $input->getOption('first-name');
        if (!$firstName) {
            $firstName = $input->isInteractive() ? $styledIo->ask('First name') : '';
        }

        $lastName = $input->getOption('last-name');
        if (!$lastName) {
            $lastName = $input->isInteractive() ? $styledIo->ask('Last name') : '';
        }

        $role = $input->getOption('role');
        if (!$role) {
            $role = $input->isInteractive()
                ? $styledIo->choice('Choose a role', [User::ROLE_ADMIN, User::ROLE_TEAM_MANAGER])
                : User::ROLE_ADMIN;
        }

        $command = new CreateUserApplicationCommand(
            null,
            $email,
            $password,
            $firstName,
            $lastName,
            $role,
            []
        );

        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));

        $output->writeln('User successfully created. ID: ' . $command->getId());
        return 0;
    }
}