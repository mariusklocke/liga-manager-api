<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugGqlSchemaCommand extends Command
{
    /** @var Schema */
    private $schema;

    public function __construct(Schema $schema)
    {
        parent::__construct();
        $this->schema = $schema;
    }

    protected function configure()
    {
        $this->setName('app:debug-gql-schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getStyledIO($input, $output)->text(SchemaPrinter::doPrint($this->schema));
    }
}