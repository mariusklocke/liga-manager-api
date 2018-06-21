<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Exception;
use JsonSerializable;
use Throwable;

class HttpException extends Exception implements JsonSerializable
{
    /** @var string */
    private $title;

    /**
     * Private constructor. Use static factory methods to create instances
     *
     * @param string $title
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    private function __construct(string $title, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->title = $title;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Factory method for creating an instance with reason "Bad Request"
     *
     * @param string $message
     * @return static
     */
    public static function createBadRequest(string $message): self
    {
        return new self('Bad Request', $message, 400);
    }

    /**
     * Factory method for creating an instance with reason "Unauthorized"
     *
     * @param string $message
     * @return static
     */
    public static function createUnauthorized(string $message): self
    {
        return new self('Unauthorized', $message, 401);
    }

    /**
     * Factory method for creating an instance with reason "Forbidden"
     *
     * @param string $message
     * @return static
     */
    public static function createForbidden(string $message): self
    {
        return new self('Forbidden', $message, 403);
    }

    /**
     * Returns an array of serializable properties
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'title'   => $this->title,
            'message' => $this->message
        ];
    }
}