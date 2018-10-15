<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CreateUserCommand as CreateUserApplicationCommand;
use Symfony\Component\Console\Input\InputArgument;
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
        $this->setName('app:create-user')->setDefinition([
            new InputArgument('email', InputArgument::REQUIRED, "Email address uniquely identifying the user"),
            new InputArgument('password', InputArgument::REQUIRED, "User password"),
            new InputArgument('first_name', InputArgument::REQUIRED, "User first name"),
            new InputArgument('last_name', InputArgument::REQUIRED, "User last name"),
            new InputArgument('role', InputArgument::REQUIRED, "User role")
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new CreateUserApplicationCommand(
            $input->getArgument('email'),
            $input->getArgument('password'),
            $input->getArgument('first_name'),
            $input->getArgument('last_name'),
            $input->getArgument('role'),
            []
        );
        $userId = $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        if (!is_string($userId)) {
            throw new \Exception('Command Bus did not return a valid user id');
        }

        $output->writeln('User successfully created. ID: ' . $userId);
        return parent::execute($input, $output);
    }
}