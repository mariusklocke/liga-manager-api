<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\EventStoreInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListEventsCommand extends Command
{
    /** @var EventStoreInterface */
    private $eventStore;

    /**
     * @param EventStoreInterface $eventStore
     */
    public function __construct(EventStoreInterface $eventStore)
    {
        parent::__construct();
        $this->eventStore = $eventStore;
    }

    protected function configure()
    {
        $this
            ->setName('app:list-events');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $events = $this->eventStore->findAll();
        if (empty($events)) {
            $output->writeln('No events found');
            return -1;
        }

        foreach ($events as $event) {
            $output->writeln(print_r($event, true));
        }

        return parent::execute($input, $output);
    }
}