<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Domain\Value\ContactPerson;

class Team extends Entity
{
    /** @var string */
    private string $name;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $createdAt;

    /** @var ContactPerson|null */
    private ?ContactPerson $contact = null;

    public function __construct(string $id, string $name)
    {
        parent::__construct($id);
        $this->setName($name);
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return ContactPerson|null
     */
    public function getContact(): ?ContactPerson
    {
        return $this->contact;
    }

    /**
     * @param ContactPerson $contact
     */
    public function setContact(ContactPerson $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        Assert::true(
            StringUtils::length($name) > 0,
            "A team's name cannot be blank",
            InvalidInputException::class
        );
        Assert::true(
            StringUtils::length($name) <= 255,
            "A team's name cannot exceed 255 characters",
            InvalidInputException::class
        );
        $this->name = $name;
    }
}
