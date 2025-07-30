<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Domain\Value\ContactPerson;

class Team extends Entity
{
    private string $name;
    private DateTimeImmutable $createdAt;
    private ?ContactPerson $contact = null;
    private ?string $logoId = null;
    private Collection $homeMatches;
    private Collection $guestMatches;

    public function __construct(string $id, string $name)
    {
        parent::__construct($id);
        $this->setName($name);
        $this->createdAt = new DateTimeImmutable();
        $this->homeMatches = new ArrayCollection();
        $this->guestMatches = new ArrayCollection();
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
            InvalidInputException::class,
            'teamNameCannotBeBlank'
        );
        Assert::true(
            StringUtils::length($name) <= 255,
            InvalidInputException::class,
            'teamNameExceedsMaxLength',
            [255]
        );
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getLogoId(): ?string
    {
        return $this->logoId;
    }

    /**
     * @param string|null $logoId
     * @return void
     */
    public function setLogoId(?string $logoId): void
    {
        $this->logoId = $logoId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Verifies if team can be safely deleted. Throws an exception otherwise.
     *
     * @return void
     */
    public function assertDeletable(): void
    {
        $this->homeMatches->isEmpty() && $this->guestMatches->isEmpty() || throw new ConflictException('teamReferencedInMatches');
    }
}
