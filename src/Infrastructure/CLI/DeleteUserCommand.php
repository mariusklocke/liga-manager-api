<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\DeleteUserCommand as DeleteUserApplicationCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUserCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:user:delete');
        $this->setDescription('Delete a user');
        $this->addArgument('userId', InputArgument::REQUIRED, 'ID of user to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var CommandBus $commandBus */
        $commandBus = $this->container->get(CommandBus::class);
        $command = new DeleteUserApplicationCommand($input->getArgument('userId'));
        $commandBus->execute($command, $this->getAuthContext());

        $this->getStyledIO($input, $output)->success('User has been deleted');

        return 0;
    }
}
