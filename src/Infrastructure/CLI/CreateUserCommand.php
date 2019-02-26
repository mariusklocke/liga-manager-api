<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CreateUserCommand as CreateUserApplicationCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $styledIo = new SymfonyStyle($input, $output);

        $command = new CreateUserApplicationCommand(
            null,
            $styledIo->ask('Email: '),
            $styledIo->ask('Password: '),
            $styledIo->ask('First name: '),
            $styledIo->ask('Last name: '),
            $styledIo->askQuestion(new ChoiceQuestion('Choose a role', ['admin', 'team_manager'], 'admin')),
            []
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));

        $output->writeln('User successfully created. ID: ' . $command->getId());
        return parent::execute($input, $output);
    }
}