<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUserCommand extends Command
{
    public const NAME = 'app:user:list';

    protected function configure(): void
    {
        $this->setDescription('List users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyledIO($input, $output);

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get(UserRepository::class);

        $users = $userRepository->findMany([]);

        if (count($users) === 0) {
            $io->text('No users found.');
        }

        $io->table(array_keys($users[0]), $users);

        return 0;
    }
}
