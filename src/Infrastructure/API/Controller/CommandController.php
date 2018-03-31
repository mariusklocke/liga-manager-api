<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Bus\SingleCommandBus;

abstract class CommandController
{
    /** @var SingleCommandBus */
    protected $commandBus;

    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
