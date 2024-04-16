<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDbCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:export');
        $this->setDescription('Export the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $dbConnection */
        $dbConnection = $this->container->get(Connection::class);

        $table = 'users';

        foreach ($dbConnection->iterateAssociative("SELECT * FROM `$table`") as $row) {
            $xmlWriter = new \XMLWriter();
            $xmlWriter->openMemory();
            $xmlWriter->startElement('row');
            foreach ($row as $column => $value) {
                $xmlWriter->startElement($column);
                $xmlWriter->text((string)$value);
                $xmlWriter->endElement();
            }
            $xmlWriter->endElement();
            $output->writeln($xmlWriter->outputMemory());
        }

        return 0;
    }
}
