<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Iterator;
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
        $rows = [];

        foreach ($this->getVersions() as $component => $version) {
            $rows[] = [$component, $version];
        }

        $this->getStyledIO($input, $output)->table($headers, $rows);

        return 0;
    }

    private function getVersions(): Iterator
    {
        yield 'Application' => $this->container->get('app.version');
        yield 'Database' => $this->container->get(Connection::class)->getServerVersion();
        yield php_uname('s') => php_uname('r');
        yield 'OpenSSL' => explode(' ', OPENSSL_VERSION_TEXT)[1];
        yield 'PHP' => PHP_VERSION;
        yield 'Redis' => $this->container->get(Redis::class)->info('server')['redis_version'];
    }
}
