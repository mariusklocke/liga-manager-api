<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use DateTimeImmutable;
use DateTimeInterface;
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
    protected $payload;

    private function __construct(string $id, DateTimeImmutable $occurredAt, array $payload)
    {
        $this->id         = $id;
        $this->occurredAt = $occurredAt;
        $this->payload    = $payload;
    }

    protected static function createFromPayload(array $payload)
    {
        return new static(Uuid::create(), new DateTimeImmutable(), $payload);
    }

    /**
     * @return DateTimeInterface
     */
    public function getOccurredAt(): DateTimeInterface
    {
        return $this->occurredAt;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return stdClass
     */
    public function jsonSerialize()
    {
        $object             = new stdClass();
        $object->id         = $this->id;
        $object->occurredAt = $this->occurredAt->getTimestamp();
        $object->payload    = $this->payload;
        $object->type       = $this->getName();
        return $object;
    }
}