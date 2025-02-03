<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Redis;

class ListVersionsCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:versions:list');
        $this->setDescription('List versions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $headers = ['Component', 'Version'];
        $rows = [
            ['Application', $this->container->get('app.version')],
            ['Database', $this->container->get(Connection::class)->getServerVersion()],
            ['Operating System', php_uname('s') . ' ' . php_uname('r')],
            ['PHP', PHP_VERSION],
            ['Redis', $this->container->get(Redis::class)->info('server')['redis_version']]
        ];

        $this->getStyledIO($input, $output)->table($headers, $rows);

        return 0;
    }
}
