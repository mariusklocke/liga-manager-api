<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDbCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:migrate');
        $this->setDescription('Migrate the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadataFactory = $entityManager->getMetadataFactory();

        $count = 0;
        foreach ($schemaTool->getUpdateSchemaSql($metadataFactory->getAllMetadata()) as $sql) {
            $output->writeln($sql);
            $entityManager->getConnection()->executeStatement($sql);
            $count++;
        }

        $this->getStyledIO($input, $output)->success("Migrated database using $count queries");

        return 0;
    }
}
