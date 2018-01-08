<?php

namespace HexagonalDream\Infrastructure\CLI;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    /** @var float */
    private $startTime;

    public function __construct()
    {
        parent::__construct(null);
        $this->startTime = microtime(true);
    }

    protected function printStats(OutputInterface $output)
    {
        $executionTime = microtime(true) - $this->startTime;
        $memory = memory_get_peak_usage() / 1024 / 1024;
        $output->writeln(sprintf('Execution time: %.1f seconds', $executionTime));
        $output->writeln(sprintf('Peak memory usage: %.1f MiB', $memory));
    }
}
