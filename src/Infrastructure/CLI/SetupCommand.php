<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\API\Security\JsonWebToken;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:setup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyledIO($input, $output);

        $env = [];
        $env['LOG_LEVEL'] = $io->ask('Enter log level', 'notice');
        $env['REDIS_HOST'] = $io->ask('Enter Redis hostname', 'redis');
        $env['MYSQL_HOST'] = $io->ask('Enter MySQL hostname', 'mariadb');
        $env['MYSQL_DATABASE'] = $io->ask('Enter MySQL database name', 'liga-manager');
        $env['MYSQL_USER'] = $io->ask('Enter MySQL user', 'dev');
        $env['MYSQL_PASSWORD'] = $io->ask('Enter MySQL password', 'dev');
        $env['EMAIL_SENDER_ADDRESS'] = $io->ask('Enter sender address for outbound email', 'noreply@example.com');
        $env['EMAIL_SENDER_NAME'] = $io->ask('Enter sender name for outbound email', 'No Reply');
        $env['EMAIL_URL'] = $io->ask('Enter URL to use for sending email', 'smtp://maildev:25');
        $env['JWT_SECRET'] = JsonWebToken::generateSecret();

        $io->section('Add the following lines to your .env file');
        foreach ($env as $name => $value) {
            $io->text("$name=$value");
        }
    }
}
