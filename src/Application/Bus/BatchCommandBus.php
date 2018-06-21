<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;

class BatchCommandBus extends CommandBus
{
    /** @var CommandInterface[] */
    private $scheduledCommands = [];

    public function schedule(CommandInterface $command)
    {
        $this->scheduledCommands[] = $command;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->transactionWrapper->transactional(function() {
            foreach ($this->scheduledCommands as $command) {
                $handler = $this->getHandler($command);
                $handler($command);
            }
        });
    }
}
