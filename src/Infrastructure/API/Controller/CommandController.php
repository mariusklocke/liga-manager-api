<?php

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\CommandBus;

abstract class CommandController
{
    /** @var CommandBus */
    protected $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
