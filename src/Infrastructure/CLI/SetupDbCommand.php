<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupDbCommand extends Command
{
    public const NAME = 'app:setup:db';

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

        system('doctrine orm:schema-tool:drop --force');
        system('doctrine dbal:run-sql "DROP TABLE IF EXISTS doctrine_migration_versions;"');
        system('doctrine-migrations migrations:migrate -n');

        $output->writeln('DB setup complete.');

        return 0;
    }
}
