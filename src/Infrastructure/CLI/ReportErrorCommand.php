<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use RuntimeException;
use HexagonalPlayground\Application\ErrorReporter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportErrorCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:error:report');
        $this->setDescription('Send an error report for testing integration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ErrorReporter $reporter */
        $reporter = $this->container->get(ErrorReporter::class);
        $reporter->report(new RuntimeException('Test error'));

        $this->getStyledIO($input, $output)->success('Error has been reported.');

        return 0;
    }
}
