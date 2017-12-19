<?php

namespace HexagonalDream\Domain;

use DateTimeImmutable;

class Team
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var DateTimeImmutable */
    private $createdAt;

    public function __construct(UuidGeneratorInterface $uuidGenerator, string $name)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->name = $name;
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function equals(Team $otherTeam) : bool
    {
        return $this->getId() === $otherTeam->getId();
    }
}
