<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrintGraphQlSchemaCommand extends Command
{
    public const NAME = 'app:graphql:schema';

    protected function configure()
    {
        $this->setDescription('Print the current GraphQL schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getStyledIO($input, $output)->text(SchemaPrinter::doPrint($this->container->get(Schema::class)));

        return 0;
    }
}