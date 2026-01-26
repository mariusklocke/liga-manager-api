<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XMLWriter;

class ExportDbCommand extends Command
{
    private static array $anonymizationMap = [
        'name' => [
            'first_name',
            'last_name',
            'contact_first_name',
            'contact_last_name'
        ],
        'email' => [
            'email',
            'contact_email'
        ],
        'password' => [
            'password'
        ],
        'phone' => [
            'contact_phone'
        ]
    ];

    protected function configure(): void
    {
        $this->setName('app:db:export');
        $this->setDescription('Export the database');
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to export file (XML)');
        $this->addOption('anonymize', 'a', InputOption::VALUE_NONE, 'Anonymize data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $outputFile = $input->getArgument('file');
        $anonymize = (bool)$input->getOption('anonymize');
        $count = $connection->transactional(function () use ($connection, $outputFile, $anonymize) {
            return $this->exportXml($connection, $outputFile, $anonymize);
        });
        $this->getStyledIO($input, $output)->success('Successfully exported ' . $count . ' records.');

        return 0;
    }

    private function exportXml(Connection $connection, string $outputFile, bool $anonymize): int
    {
        $writer = new XMLWriter();
        $writer->openUri($outputFile);
        $writer->setIndent(true);
        $writer->startDocument();
        $writer->startElement('database');

        $schemaManager = $connection->createSchemaManager();
        $platform = $connection->getDatabasePlatform();
        $count = 0;
        foreach ($schemaManager->introspectTables() as $table) {
            $types = [];
            foreach ($schemaManager->introspectTableColumns($table->getObjectName()) as $column) {
                $mappedType = $this->mapType($column->getType());
                if (null === $mappedType) {
                    throw new RuntimeException(sprintf(
                        'Unsupported type %s for %s in %s',
                        get_class($column->getType()),
                        $column->getObjectName()->getIdentifier()->getValue(),
                        $table->getObjectName()->getUnqualifiedName()->getValue()
                    ));
                }
                $types[$column->getObjectName()->getIdentifier()->getValue()] = $mappedType;
            }
            $writer->startElement('table');
            $writer->writeAttribute('name', $table->getObjectName()->getUnqualifiedName()->getValue());
            $query = $connection->createQueryBuilder()->select('*')->from($table->getObjectName()->toSQL($platform))->getSQL();
            foreach ($connection->iterateAssociative($query) as $row) {
                if ($anonymize) {
                    $row = $this->anonymize($row);
                }

                $writer->startElement('row');

                foreach ($row as $column => $value) {
                    $type = $types[$column];
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
        $value = (string)$value;

        if ($type === 'binary') {
            $value = bin2hex($value);
        }

        return $value;
    }

    private function mapType(Type $type): ?string
    {
        if ($type instanceof StringType || $type instanceof TextType) {
            return 'string';
        }
        if ($type instanceof IntegerType) {
            return 'integer';
        }
        if ($type instanceof FloatType) {
            return 'float';
        }
        if ($type instanceof DateTimeImmutableType || $type instanceof DateImmutableType || $type instanceof DateTimeType || $type instanceof DateType) {
            return 'string';
        }
        if ($type instanceof BinaryType || $type instanceof BlobType) {
            return 'binary';
        }
        if ($type instanceof JsonType) {
            return 'json';
        }
        return null;
    }

    private function anonymize(array $record): array
    {
        foreach (self::$anonymizationMap['name'] as $property) {
            if (isset($record[$property])) {
                $record[$property] = 'Anonymized';
            }
        }
        foreach (self::$anonymizationMap['email'] as $property) {
            if (isset($record[$property])) {
                $record[$property] = uniqid() . '@example.com';
            }
        }
        foreach (self::$anonymizationMap['phone'] as $property) {
            if (isset($record[$property])) {
                $record[$property] = '+49' . sprintf('%d', random_int(100000, 999999));
            }
        }
        foreach (self::$anonymizationMap['password'] as $property) {
            if (isset($record[$property])) {
                $record[$property] = password_hash($this->generatePassword(random_int(16,24)), PASSWORD_BCRYPT);
            }
        }

        return $record;
    }

    private function generatePassword(int $length): string
    {
        $characters = array_merge(
            range('0', '9'),
            range('a', 'z'),
            range('A', 'Z')
        );

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, count($characters) - 1)];
        }

        return $password;
    }
}
