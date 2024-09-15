<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XMLWriter;

class ExportDbCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:export');
        $this->setDescription('Export the database');
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to export file (XML)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $count = $connection->transactional(function () use ($connection, $input) {
            return $this->exportXml($connection, $input);
        });
        $this->getStyledIO($input, $output)->success('Successfully exported ' . $count . ' records.');

        return 0;
    }

    private function exportXml(Connection $connection, InputInterface $input): int
    {
        $writer = new XMLWriter();
        $writer->openUri('file://' . $input->getArgument('file'));
        $writer->setIndent(true);
        $writer->startDocument();
        $writer->startElement('database');

        $count = 0;
        foreach ($connection->fetchFirstColumn("SHOW TABLES") as $table) {
            $types = [];
            foreach ($connection->fetchAllAssociative("DESCRIBE $table") as $column) {
                $types[$column['Field']] = $column['Type'];
            }
            $writer->startElement('table');
            $writer->writeAttribute('name', $table);
            foreach ($connection->iterateAssociative("SELECT * FROM `$table`") as $row) {
                $writer->startElement('row');

                foreach ($row as $column => $value) {
                    $type = $this->mapType($types[$column]);
                    $writer->startElement('column');
                    $writer->writeAttribute('name', $column);
                    $writer->writeAttribute('type', $type);
                    if ($type === 'binary') {
                        $writer->writeAttribute('encoding', 'hex');
                    }
                    if ($value === null) {
                        $writer->writeAttribute('null', '');
                    } else {
                        $value = $this->encodeValue($types[$column], $value);
                        if ($value !== '') {
                            $writer->text($value);
                        }
                    }
                    $writer->endElement();
                }

                $writer->endElement();
                $count++;
            }
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();

        return $count;
    }

    private function encodeValue(string $type, mixed $value): string
    {
        if (str_starts_with($type, 'varchar') || str_starts_with($type, 'longtext')) {
            return (string)$value;
        }
        if (str_starts_with($type, 'int')) {
            return (string)$value;
        }
        if (str_starts_with($type, 'float') || str_starts_with($type, 'double')) {
            return (string)$value;
        }
        if (str_starts_with($type, 'datetime') || str_starts_with($type, 'date')) {
            return (string)$value;
        }
        if (str_starts_with($type, 'varbinary')) {
            return bin2hex($value);
        }
        throw new InvalidArgumentException('Unsupported type: ' . $type);
    }

    private function mapType(string $type): string
    {
        if (str_starts_with($type, 'varchar') || str_starts_with($type, 'longtext')) {
            return 'string';
        }
        if (str_starts_with($type, 'int')) {
            return 'integer';
        }
        if (str_starts_with($type, 'float') || str_starts_with($type, 'double')) {
            return 'float';
        }
        if (str_starts_with($type, 'datetime') || str_starts_with($type, 'date')) {
            return 'string';
        }
        if (str_starts_with($type, 'varbinary')) {
            return 'binary';
        }
        throw new InvalidArgumentException('Unsupported type: ' . $type);
    }
}
