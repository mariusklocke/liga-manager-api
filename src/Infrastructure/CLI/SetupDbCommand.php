<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Symfony\Component\Console\Input\ArrayInput;
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

        $this->getApplication()->find('app:create-user')->run(new ArrayInput([
            '--email' => getenv('ADMIN_EMAIL'),
            '--password' => getenv('ADMIN_PASSWORD'),
            '--role' => 'admin',
            '--first-name' => 'admin',
            '--last-name' => 'admin'
        ]), $output);

        $output->writeln('DB setup complete. Please note the following credentials for the initial admin user.');
        $output->writeln('Email: ' . getenv('ADMIN_EMAIL'));
        $output->writeln('Password: ' . getenv('ADMIN_PASSWORD'));

        return 0;
    }
}
