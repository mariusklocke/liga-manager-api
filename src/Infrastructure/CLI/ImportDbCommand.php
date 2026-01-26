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
        $platform = $connection->getDatabasePlatform();
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->introspectTables();

        $inputFile = $input->getArgument('file');
        $chunkSize = (int)$input->getOption('chunk-size');

        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $connection->executeQuery(
                    $platform->getDropForeignKeySQL($foreignKey->getObjectName()->toSQL($platform), $table->getObjectName()->toSQL($platform))
                );
            }
        }

        $count = $connection->transactional(function () use ($connection, $inputFile, $chunkSize) {
            return $this->importXml($connection, $inputFile, $chunkSize);
        });

        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $connection->executeQuery(
                    $platform->getCreateForeignKeySQL($foreignKey, $table->getObjectName()->toSQL($platform))
                );
            }
        }

        $this->getStyledIO($input, $output)->success('Successfully imported ' . $count . ' records.');

        return 0;
    }

    private function importXml(Connection $connection, string $inputFile, int $chunkSize): int
    {
        $table = null;
        $row = null;
        $data = null;
        $types = null;
        $count = 0;
        $reader = new XMLReader();
        $reader->open($inputFile);
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
        $columns = [];
        foreach (array_keys($data[0]) as $column) {
            $columns[] = $connection->quoteSingleIdentifier($column);
        }
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
            $connection->quoteSingleIdentifier($table),
            implode(',', $columns),
            implode(',', $values)
        );

        $connection->executeQuery($query, $params, $mappedTypes);
    }

    private function clearTable(Connection $connection, string $table): void
    {
        $connection->executeQuery(sprintf("DELETE FROM %s", $connection->quoteSingleIdentifier($table)));
    }

    private function mapType(string $type): string
    {
        $typeMap = [
            'string' => Types::STRING,
            'integer' => Types::INTEGER,
            'float' => Types::FLOAT,
            'boolean' => Types::BOOLEAN,
            'binary' => Types::BINARY,
            'json' => Types::JSON
        ];

        if (!isset($typeMap[$type])) {
            throw new InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        return $typeMap[$type];
    }

    private function decodeValue(string $type, string $value): string|int|float|bool|array
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
            case 'json':
                return json_decode($value, true);
        }
        throw new InvalidArgumentException(sprintf('Unknown type "%s"', $type));
    }
}
