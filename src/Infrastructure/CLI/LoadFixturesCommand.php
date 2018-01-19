<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\FixtureLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends Command
{
    /** @var FixtureLoader */
    private $fixtureLoader;

    /**
     * @param FixtureLoader $fixtureLoader
     */
    public function __construct(FixtureLoader $fixtureLoader)
    {
        $this->fixtureLoader = $fixtureLoader;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:load-fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fixtureLoader->__invoke();
        $output->writeln('Fixtures successfully loaded');
    }
}
