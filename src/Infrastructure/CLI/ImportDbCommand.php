<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XMLReader;

class ImportDbCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:import');
        $this->setDescription('Import a database');
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to import file (XML)');
        $this->addOption('chunk-size', null, InputOption::VALUE_OPTIONAL, 'Chunk size', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $inputFile  = $input->getArgument('file');
        $chunkSize  = (int)$input->getOption('chunk-size');

        $this->setForeignKeyChecks($connection, false);
        $count = $connection->transactional(function () use ($connection, $inputFile, $chunkSize) {
            return $this->importXml($connection, $inputFile, $chunkSize);
        });
        $this->setForeignKeyChecks($connection, true);
        $this->getStyledIO($input, $output)->success('Successfully imported ' . $count . ' records.');

        return 0;
    }

    private function setForeignKeyChecks(Connection $connection, bool $enabled): void
    {
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=' . ($enabled ? '1' : '0'));
    }

    private function importXml(Connection $connection, string $inputFile, int $chunkSize): int
    {
        $table = null;
        $row = null;
        $data = null;
        $types = null;
        $count = 0;
        $reader = new XMLReader();
        $reader->open('file://' . $this->makePathAbsolute($inputFile));
        while ($reader->read()) {
            // Start element
            if ($reader->nodeType === XMLReader::ELEMENT) {
                switch ($reader->name) {
                    case 'table':
                        $table = $reader->getAttribute('name');
                        $types = [];
                        $data = [];
                        $this->clearTable($connection, $table);
                        break;
                    case 'row':
                        $row = [];
                        break;
                    case 'column':
                        $value = $reader->readString();
                        $name = $reader->getAttribute('name');
                        $type = $reader->getAttribute('type');
                        if ($reader->getAttribute('null') !== null) {
                            $row[$name] = null;
                        } else {
                            $row[$name] = $this->decodeValue($type, $value);
                        }
                        $types[$name] = $this->mapType($type);
                        break;
                }
            }
            // End element
            if ($reader->nodeType === XMLReader::END_ELEMENT) {
                switch ($reader->name) {
                    case 'table':
                        if (count($data) > 0) {
                            $this->insertIntoTable($connection, $table, $data, $types);
                            $data = [];
                        }
                        break;
                    case 'row':
                        $data[] = $row;
                        $count++;
                        if (count($data) === $chunkSize) {
                            $this->insertIntoTable($connection, $table, $data, $types);
                            $data = [];
                        }
                        break;
                }
            }
        }
        $reader->close();

        return $count;
    }

    private function insertIntoTable(Connection $connection, string $table, array $data, array $types): void
    {
        $columns = array_map([$connection, 'quoteIdentifier'], array_keys($data[0]));
        $params = [];
        $values = [];
        $mappedTypes = [];
        foreach ($data as $index => $row) {
            $values[$index] = [];
            foreach ($row as $column => $value) {
                $params[$column . '_' . $index] = $value;
                $values[$index][] = ':' . $column . '_' . $index;
                $mappedTypes[$column . '_' . $index] = $types[$column];
            }
            $values[$index] = '(' . implode(',', $values[$index]) . ')';
        }

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES %s",
            $connection->quoteIdentifier($table),
            implode(',', $columns),
            implode(',', $values)
        );

        $connection->executeQuery($query, $params, $mappedTypes);
    }

    private function clearTable(Connection $connection, string $table): void
    {
        $connection->executeQuery(sprintf("DELETE FROM %s", $connection->quoteIdentifier($table)));
    }

    private function mapType(string $type): string
    {
        $typeMap = [
            'string' => Types::STRING,
            'integer' => Types::INTEGER,
            'float' => Types::FLOAT,
            'boolean' => Types::BOOLEAN,
            'binary' => Types::BINARY
        ];

        if (!isset($typeMap[$type])) {
            throw new InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        return $typeMap[$type];
    }

    private function decodeValue(string $type, string $value): string|int|float|bool
    {
        switch ($type) {
            case 'string':
                return $value;
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'boolean':
                return (bool)$value;
            case 'binary':
                return hex2bin($value);
        }
        throw new InvalidArgumentException(sprintf('Unknown type "%s"', $type));
    }

    private function makePathAbsolute(string $filePath): string
    {
        if ($filePath[0] === DIRECTORY_SEPARATOR) {
            return $filePath;
        } else {
            return getcwd() . DIRECTORY_SEPARATOR . $filePath;
        }
    }
}
