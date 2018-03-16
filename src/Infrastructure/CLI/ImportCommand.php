<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\BatchCommandBus;
use HexagonalPlayground\Application\Command\CommandInterface;
use Iterator;
use OverflowException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ImportCommand extends Command
{
    const MAX_IMPORT_ROWS = 8192;

    /** @var BatchCommandBus */
    private $commandBus;

    public function __construct(BatchCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileIterator = $this->createFileIterator($input->getArgument('file'));
        $count = 0;
        foreach ($fileIterator as $row) {
            if ($count === self::MAX_IMPORT_ROWS) {
                throw new OverflowException(sprintf('Cannot import more than %d rows at once', $count));
            }

            $this->commandBus->schedule($this->createApplicationCommand($row));
            $count++;
        }
        $this->commandBus->execute();

        if ($count > 0) {
            $output->writeln(sprintf('Successfully imported %d rows', $count));
            $this->printStats($output);
            return 0;
        }

        $output->writeln("Couldn't find something to import");
        return 1;
    }

    abstract protected function createFileIterator(string $filePath) : Iterator;

    abstract protected function createApplicationCommand(array $row) : CommandInterface;
}