<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDbCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:migrate');
        $this->setDescription('Migrate the database');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only print the migration queries instead of executing them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadataFactory = $entityManager->getMetadataFactory();
        $dryRun = (bool)$input->getOption('dry-run');

        $count = 0;
        foreach ($schemaTool->getUpdateSchemaSql($metadataFactory->getAllMetadata()) as $sql) {
            $output->writeln($sql);
            if (!$dryRun) {
                $entityManager->getConnection()->executeStatement($sql);
            }
            $count++;
        }

        if ($dryRun) {
            $output->writeln("Dry-Run activated: $count queries were generated. No queries were executed.");
            return 0;
        }

        if ($count > 0) {
            $output->writeln("Database successfully migrated using $count queries.");
        } else {
            $output->writeln("Database already up to date. Nothing to migrate.");
        }

        return 0;
    }
}
