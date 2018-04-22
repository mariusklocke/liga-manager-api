<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Exception;

use JsonSerializable;

interface HttpException extends JsonSerializable
{
    public function getStatusCode(): int;
}