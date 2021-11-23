<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Environment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunServerCommand extends Command
{
    public const NAME = 'app:server:run';

    protected function configure()
    {
        $this->setDescription('Run PHP-internal webserver for development');
        $this->addArgument('socket', InputArgument::OPTIONAL, 'Socket to bind to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $socket = $input->getArgument('socket') ?? '0.0.0.0:8080';
        $docRoot = Environment::get('APP_HOME') . '/public';

        system(sprintf('php -S %s -t %s %s', $socket, $docRoot, $docRoot . '/index.php'));

        return 0;
    }
}
