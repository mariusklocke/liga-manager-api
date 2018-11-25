<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\API\Security\UserAware;

abstract class CommandController
{
    use UserAware;
    use TypeAssert;
    use ResponseFactoryTrait;

    /** @var CommandBus */
    protected $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
