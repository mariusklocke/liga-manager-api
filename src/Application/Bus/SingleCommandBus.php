<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;

class SingleCommandBus extends CommandBus
{
    /**
     * @param CommandInterface $command
     * @return mixed
     */
    public function execute(CommandInterface $command)
    {
        $handler = $this->getHandler($command);
        return $this->transactionWrapper->transactional(function() use ($handler, $command) {
            return $handler($command);
        });
    }
}
