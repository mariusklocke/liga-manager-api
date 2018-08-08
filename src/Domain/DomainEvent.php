<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use DateTimeInterface;
use HexagonalPlayground\Domain\Util\Uuid;

abstract class DomainEvent
{
    /** @var string */
    private $id;

    /** @var DateTimeImmutable */
    private $occurredAt;

    /** @var array */
    protected $payload;

    public function __construct(string $id, DateTimeImmutable $occurredAt, array $payload)
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
}