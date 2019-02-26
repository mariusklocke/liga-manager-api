<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Domain\User;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    /** @var float */
    private $startTime;

    /** @var User */
    private $user;

    public function __construct()
    {
        parent::__construct(null);
        $this->startTime = microtime(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$output->isQuiet()) {
            $this->printStats($output);
        }
        return 0;
    }

    private function printStats(OutputInterface $output)
    {
        $executionTime = microtime(true) - $this->startTime;
        $memory = memory_get_peak_usage() / 1024 / 1024;
        $output->writeln(sprintf('Execution time: %.1f seconds', $executionTime));
        $output->writeln(sprintf('Peak memory usage: %.1f MiB', $memory));
    }

    protected function getCliUser(): User
    {
        if (null === $this->user) {
            $this->user = new User(
                'cli',
                'cli@example.com',
                '123456',
                'CLI',
                $this->getName()
            );
            $this->user->setRole(User::ROLE_ADMIN);
        }
        return $this->user;
    }
}
