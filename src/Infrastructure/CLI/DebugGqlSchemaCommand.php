<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugGqlSchemaCommand extends Command
{
    public const NAME = 'app:debug-gql-schema';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getStyledIO($input, $output)->text(SchemaPrinter::doPrint($this->container->get(Schema::class)));
    }
}