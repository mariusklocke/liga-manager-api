<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\BatchCommandBus;
use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\Filesystem\CsvFileReader;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMatchesCommand extends Command
{
    const MAX_IMPORT_ROWS = 8192;

    /** @var BatchCommandBus */
    private $commandBus;

    public function __construct(BatchCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import-matches')
            ->setDefinition([
                new InputArgument('file', InputArgument::REQUIRED, "Path to the CSV file to import")
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $fileIterator = $this->createFileIterator($input->getArgument('file'));
        } catch (InvalidArgumentException $e) {
            $output->writeln($e->getMessage());
            return -2;
        }
        $count = 0;
        foreach ($fileIterator as $row) {
            if (count($row) < 4) {
                continue;
            }

            if ($count === self::MAX_IMPORT_ROWS) {
                $output->writeln(sprintf('Cannot import more than %d rows at once', $count));
                return -4;
            }

            try {
                $this->commandBus->schedule(new CreateSingleMatchCommand($row[0], (int)$row[1], $row[2], $row[3]));
            } catch (NotFoundException $e) {
                $output->writeln($e->getMessage());
                return -3;
            }
            $count++;
        }
        $this->commandBus->execute();

        if ($count > 0) {
            $output->writeln(sprintf('Successfully imported %d matches', $count));
            $this->printStats($output);
            return 0;
        }

        $output->writeln("Couldn't find a match to import");
        return -1;
    }

    private function createFileIterator(string $filePath)
    {
        return new CsvFileReader($filePath);
    }
}
