<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Infrastructure\Filesystem\CsvFileReader;
use InvalidArgumentException;
use Iterator;
use Symfony\Component\Console\Input\InputArgument;

class ImportMatchesCommand extends ImportCommand
{
    protected function configure()
    {
        $this
            ->setName('app:import-matches')
            ->setDefinition([
                new InputArgument('file', InputArgument::REQUIRED, "Path to the CSV file to import")
            ]);
    }

    protected function createFileIterator(string $filePath) : Iterator
    {
        return new CsvFileReader($filePath);
    }

    protected function createApplicationCommand(array $row): CommandInterface
    {
        if (count($row) < 4) {
            throw new InvalidArgumentException('Malformed match row format');
        }

        list($seasonId, $matchDay, $homeTeamId, $guestTeamId) = $row;
        if (!is_numeric($matchDay)) {
            throw new InvalidArgumentException('MatchDay has to be a numeric value');
        }
        if ($matchDay < 0) {
            throw new InvalidArgumentException('MatchDay has to be positive integer');
        }

        return new CreateSingleMatchCommand($seasonId, (int)$matchDay, $homeTeamId, $guestTeamId);
    }
}
