<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use HexagonalPlayground\Domain\ExceptionInterface;

class UniquenessException extends \Exception implements ExceptionInterface
{
    /**
     * @inheritdoc
     */
    public function getHttpStatusCode(): int
    {
        return 400;
    }
}