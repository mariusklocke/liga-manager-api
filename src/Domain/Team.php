<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\TeamCreated;
use HexagonalPlayground\Domain\Event\TeamRenamed;
use HexagonalPlayground\Domain\Util\Assert;

class Team
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var DateTimeImmutable */
    private $createdAt;

    /** @var ContactPerson */
    private $contact;

    public function __construct(string $id, string $name)
    {
        Assert::minLength($id, 1, "A team's id cannot be blank");
        $this->id = $id;
        $this->setName($name);
        $this->createdAt = new DateTimeImmutable();
        Publisher::getInstance()->publish(TeamCreated::create($this->id));
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
     */
    public function rename(string $newName)
    {
        $oldName = $this->name;
        if ($newName !== $oldName) {
            $this->setName($newName);
            Publisher::getInstance()->publish(TeamRenamed::create($this->id, $oldName, $newName));
        }
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

    /**
     * @param string $name
     */
    private function setName(string $name): void
    {
        Assert::minLength($name, 1, "A team's name cannot be blank");
        Assert::maxLength($name, 255, "A team's name cannot exceed 255 characters");
        $this->name = $name;
    }
}
