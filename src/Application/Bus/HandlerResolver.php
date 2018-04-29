<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;

interface HandlerResolver
{
    /**
     * Returns the appropriate command handler for the given command
     *
     * @param CommandInterface $command
     * @return object
     */
    public function resolve(CommandInterface $command);
}