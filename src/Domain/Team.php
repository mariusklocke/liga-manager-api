<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\TeamContactUpdated;
use HexagonalPlayground\Domain\Event\TeamCreated;
use HexagonalPlayground\Domain\Event\TeamRenamed;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\ContactPerson;

class Team extends Entity
{
    /** @var string */
    private $name;

    /** @var DateTimeImmutable */
    private $createdAt;

    /** @var ContactPerson|null */
    private $contact;

    public function __construct(string $id, string $name)
    {
        parent::__construct($id);
        $this->setName($name);
        $this->createdAt = new DateTimeImmutable();
        Publisher::getInstance()->publish(TeamCreated::create($this->id));
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
     * @param ContactPerson $contact
     */
    public function setContact(ContactPerson $contact): void
    {
        if (null === $this->contact || !$this->contact->equals($contact)) {
            Publisher::getInstance()->publish(TeamContactUpdated::create($this->id, $this->contact, $contact));
            $this->contact = $contact;
        }
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
