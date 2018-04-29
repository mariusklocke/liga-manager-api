<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use DateTimeInterface;

abstract class DomainEvent
{
    /** @var string */
    private $id;

    /** @var DateTimeImmutable */
    private $occurredAt;

    public function __construct()
    {
        $this->id         = Uuid::create();
        $this->occurredAt = new DateTimeImmutable();
    }

    /**
     * @return DateTimeInterface
     */
    public function occurredAt(): DateTimeInterface
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
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'occurredAt' => $this->occurredAt->format(DATE_ATOM)
        ];
    }
}