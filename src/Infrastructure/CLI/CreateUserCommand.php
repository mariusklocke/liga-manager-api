<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\SingleCommandBus;
use HexagonalPlayground\Application\Command\CreateUserCommand as CreateUserApplicationCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    /** @var SingleCommandBus */
    private $commandBus;

    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:create-user')->setDefinition([
            new InputArgument('email', InputArgument::REQUIRED, "Email address uniquely identifying the user")
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $this->commandBus->execute(new CreateUserApplicationCommand($input->getArgument('email')));
        if (is_string($userId)) {
            $output->writeln('User successfully created. ID: ' . $userId);
            return;
        }

        throw new \Exception('Command Bus did not return a valid user id');
    }
}