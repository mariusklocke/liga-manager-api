<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Domain\Value\ContactPerson;
use HexagonalPlayground\Domain\Value\GeographicLocation;

class Pitch extends Entity
{
    /** @var string */
    private string $label;

    /** @var GeographicLocation|null */
    private ?GeographicLocation $location = null;

    /** @var ContactPerson|null */
    private ?ContactPerson $contact = null;

    /** @var Collection|MatchEntity[] */
    private Collection $matches;

    public function __construct(string $id, string $label, ?GeographicLocation $location = null)
    {
        parent::__construct($id);
        StringUtils::length($label) > 0 || throw new InvalidInputException('pitchLabelCannotBeBlank');
        StringUtils::length($label) <= 255 || throw new InvalidInputException('pitchLabelExceedsMaxLength', [255]);
        $this->label = $label;
        $this->location = $location;
        $this->matches = new ArrayCollection();
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
     * @param MatchEntity $match
     */
    public function addMatch(MatchEntity $match): void
    {
        if (!$this->matches->containsKey($match->getId())) {
            $this->matches->add($match);
        }
    }

    /**
     * @param MatchEntity $match
     */
    public function removeMatch(MatchEntity $match): void
    {
        $this->matches->remove($match->getId());
    }

    /**
     * Verifies if pitch can be safely deleted. Throws an exception otherwise.
     */
    public function assertDeletable(): void
    {
        $this->matches->isEmpty() || throw new ConflictException('pitchUsedInMatches');
    }
}
