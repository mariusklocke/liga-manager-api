<?php

namespace HexagonalDream\Application\Bus;

use HexagonalDream\Application\Command\CommandInterface;
use HexagonalDream\Application\Exception\CommandBusException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;

class SingleCommandBus extends CommandBus
{
    /**
     * @param CommandInterface $command
     * @return mixed
     * @throws CommandBusException If the container does not contain a valid handler for given Command class
     * @throws PersistenceExceptionInterface
     */
    public function execute(CommandInterface $command)
    {
        $handler = $this->getHandler($command);
        return $this->persistence->transactional(function() use ($handler, $command) {
            return $handler->handle($command);
        });
    }
}
