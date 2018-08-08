<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\Uuid;

class Team
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string[] */
    private $previousNames;

    /** @var DateTimeImmutable */
    private $createdAt;

    /** @var ContactPerson */
    private $contact;

    public function __construct(string $name)
    {
        Assert::minLength($name, 1, "A team's name cannot be blank");
        Assert::maxLength($name, 255, "A team's name cannot exceed 255 characters");
        $this->id = Uuid::create();
        $this->name = $name;
        $this->previousNames = [];
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $newName
     * @return Team
     */
    public function rename(string $newName) : Team
    {
        $this->previousNames[] = $this->name;
        $this->name = $newName;
        return $this;
    }

    /**
     * @param Team $otherTeam
     * @return bool
     */
    public function equals(Team $otherTeam) : bool
    {
        return $this->getId() === $otherTeam->getId();
    }

    /**
     * @param ContactPerson $person
     */
    public function setContact(ContactPerson $person): void
    {
        $this->contact = $person;
    }
}
