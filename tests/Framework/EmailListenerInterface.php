<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Application\Email\MessageInterface;

interface EmailListenerInterface
{
    /**
     * @param callable $callable
     * @return MessageInterface[]
     */
    public function listen(callable $callable): array;
}