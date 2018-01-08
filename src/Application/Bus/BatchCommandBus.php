<?php

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Exception\CommandBusException;
use HexagonalPlayground\Application\Exception\PersistenceExceptionInterface;

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
     * @throws PersistenceExceptionInterface
     * @throws CommandBusException
     */
    public function execute()
    {
        return $this->persistence->transactional(function() {
            foreach ($this->scheduledCommands as $command) {
                $this->getHandler($command)->handle($command);
            }
        });
    }
}
