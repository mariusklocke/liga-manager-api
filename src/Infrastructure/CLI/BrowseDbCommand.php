<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BrowseDbCommand extends Command
{
    public const NAME = 'app:db:browse';

    protected function configure(): void
    {
        $this->setDescription('Browse the database interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $dbConnection */
        $dbConnection = $this->container->get(Connection::class);

        $styledIo = $this->getStyledIO($input, $output);

        $table = $styledIo->choice(
            'Please select a table',
            $dbConnection->fetchFirstColumn("SHOW TABLES")
        );

        $styledIo->table(
            $dbConnection->fetchFirstColumn("SHOW COLUMNS FROM `$table`"),
            $dbConnection->fetchAllNumeric("SELECT * FROM `$table`")
        );

        return 0;
    }
}
