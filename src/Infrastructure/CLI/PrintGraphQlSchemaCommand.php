<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintGraphQlSchemaCommand extends Command
{
    public const NAME = 'app:graphql:schema';

    protected function configure()
    {
        $this->setDescription('Print the current GraphQL schema');
        $this->addOption('v2', null, InputOption::VALUE_NONE, 'Print v2 schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('v2')) {
            $schema = $this->container->get(\HexagonalPlayground\Infrastructure\API\GraphQL\v2\Schema::class);
        } else {
            $schema = $this->container->get(Schema::class);
        }

        $output->writeln(SchemaPrinter::doPrint($schema));

        return 0;
    }
}
