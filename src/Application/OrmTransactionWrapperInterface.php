<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

interface OrmTransactionWrapperInterface
{
    /**
     * Wraps the execution of a callable in a transaction
     *
     * @param callable $callable
     * @return mixed Whatever the callable returns
     */
    public function transactional(callable $callable);
}