<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Exception;

use Exception;

class BadRequestException extends Exception implements HttpException
{
    /** @var array */
    private $details;

    /**
     * @param string $message
     * @param array $details
     */
    public function __construct(string $message = "", array $details = [])
    {
        parent::__construct($message);
        $this->details = $details;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return 400;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $array = [
            'title'   => 'Bad Request',
            'message' => $this->message
        ];
        if (!empty($this->details)) {
            $array['details'] = $this->details;
        }
        return $array;
    }
}