<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Domain\Util\Uuid;
use JsonSerializable;
use stdClass;

abstract class Event implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var DateTimeImmutable */
    private $occurredAt;

    /** @var array */
    private $payload;

    /**
     * @param string $id
     * @param DateTimeImmutable $occurredAt
     * @param array $payload
     */
    private function __construct(string $id, DateTimeImmutable $occurredAt, array $payload)
    {
        $this->id         = $id;
        $this->occurredAt = $occurredAt;
        $this->payload    = $payload;
    }

    /**
     * @param array $payload
     * @return static
     */
    protected static function createFromPayload(array $payload)
    {
        return new static(Uuid::create(), new DateTimeImmutable(), $payload);
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        $className = StringUtils::stripNamespace(static::class);
        return StringUtils::camelCaseToSeparatedLowercase($className);
    }

    /**
     * @return stdClass
     */
    public function jsonSerialize()
    {
        $object             = new stdClass();
        $object->id         = $this->id;
        $object->occurredAt = $this->occurredAt->getTimestamp();
        $object->payload    = $this->payload;
        $object->type       = static::getName();
        return $object;
    }
}