<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

interface OrmTransactionWrapperInterface
{
    /**
     * @param callable $callable
     * @return mixed Whatever the callable returns
     */
    public function transactional(callable $callable);
}