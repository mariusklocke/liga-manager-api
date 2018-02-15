<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Exception\CommandBusException;

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
