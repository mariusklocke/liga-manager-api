<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\SingleCommandBus;
use HexagonalPlayground\Application\Command\LoadFixturesCommand as LoadFixturesApplicationCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends Command
{
    /** @var SingleCommandBus */
    private $commandBus;

    /**
     * @param SingleCommandBus $commandBus
     */
    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:load-fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandBus->execute(new LoadFixturesApplicationCommand());
        $output->writeln('Fixtures successfully loaded');
    }
}
