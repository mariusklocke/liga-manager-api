<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BrowseDbCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:browse');
        $this->setDescription('Browse the database interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $dbConnection */
        $dbConnection = $this->container->get(Connection::class);
        $schemaManager = $dbConnection->createSchemaManager();
        $queryBuilder = $dbConnection->createQueryBuilder();

        $styledIo = $this->getStyledIO($input, $output);

        $table = $styledIo->choice('Please select a table', $schemaManager->listTableNames());
        $columns = array_keys($schemaManager->listTableColumns($table));
        $query = $queryBuilder->select('*')->from($table)->getSQL();
        $data = $dbConnection->fetchAllNumeric($query);
        
        $styledIo->table($columns, $data);

        return 0;
    }
}
