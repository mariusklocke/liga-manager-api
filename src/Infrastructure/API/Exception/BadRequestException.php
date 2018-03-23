<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Exception;

use JsonSerializable;
use Exception;

class BadRequestException extends Exception implements JsonSerializable
{
    /**
     * @param string $message
     */
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode() : int
    {
        return 400;
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'title'   => 'Bad Request',
            'message' => $this->message
        ];
    }
}