<?php

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Bus\SingleCommandBus;

abstract class CommandController
{
    /** @var SingleCommandBus */
    protected $commandBus;

    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
