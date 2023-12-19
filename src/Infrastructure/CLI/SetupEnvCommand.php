<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Filesystem\FileStream;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupEnvCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:env:setup');
        $this->setDescription('Setup environment config interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var FilesystemService $filesystem */
        $filesystem = $this->container->get(FilesystemService::class);
        $io = $this->getStyledIO($input, $output);

        $env = [];
        $env['LOG_LEVEL'] = $io->ask('Enter log level', 'debug');
        $env['LOG_PATH'] = $io->ask('Enter log path', 'php://stdout');
        $env['REDIS_HOST'] = $io->ask('Enter Redis hostname', 'redis');
        $env['MYSQL_HOST'] = $io->ask('Enter MySQL hostname', 'mariadb');
        $env['MYSQL_DATABASE'] = $io->ask('Enter MySQL database name', 'liga-manager');
        $env['MYSQL_USER'] = $io->ask('Enter MySQL user', 'dev');
        $env['MYSQL_PASSWORD'] = $io->ask('Enter MySQL password', 'dev');
        $env['EMAIL_SENDER_ADDRESS'] = $io->ask('Enter sender address for outbound email', 'noreply@example.com');
        $env['EMAIL_SENDER_NAME'] = $io->ask('Enter sender name for outbound email', 'No Reply');
        $env['EMAIL_URL'] = $io->ask('Enter URL to use for sending email', 'smtp://maildev:25?verify_peer=0');
        $env['JWT_SECRET'] = bin2hex(random_bytes(32));

        $envPath = $filesystem->joinPaths([$this->container->get('app.home'), '.env']);
        if ($filesystem->isWritable($envPath)) {
            $confirmed = $io->confirm(
                'Your .env file seems to be writeable. Do want to write your configuration directly?',
                false
            );

            if ($confirmed) {
                $stream = $filesystem->openFile($envPath, 'w');

                foreach ($env as $name => $value) {
                    $stream->write("$name=$value\n");
                }

                $stream->close();

                return 0;
            }
        }

        $io->section('Add the following lines to your .env file');
        foreach ($env as $name => $value) {
            $io->text("$name=$value");
        }

        return 0;
    }
}
