<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Bus\SingleCommandBus;
use HexagonalPlayground\Infrastructure\API\Security\UserAware;

abstract class CommandController
{
    use UserAware;
    use TypeAssert;

    /** @var SingleCommandBus */
    protected $commandBus;

    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
