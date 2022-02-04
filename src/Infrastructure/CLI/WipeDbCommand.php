<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WipeDbCommand extends Command
{
    public const NAME = 'app:db:wipe';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyledIO($input, $output);

        if ($input->isInteractive()) {
            if (!$io->confirm(
                'Warning: You are about to delete the current database. Are you sure you want to continue?',
                false
            )) {
                return 0;
            }
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        foreach ($connection->fetchFirstColumn('SHOW TABLES') as $table) {
            $connection->executeStatement("DROP TABLE `$table`");
        }

        $io->success('Successfully dropped all tables');

        return 0;
    }
}
