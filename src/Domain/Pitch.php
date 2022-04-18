<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\ContactPerson;
use HexagonalPlayground\Domain\Value\GeographicLocation;

class Pitch extends Entity
{
    /** @var string */
    private string $label;

    /** @var GeographicLocation */
    private GeographicLocation $location;

    /** @var ContactPerson|null */
    private ?ContactPerson $contact = null;

    /** @var Collection|MatchEntity[] */
    private Collection $matches;

    public function __construct(string $id, string $label, GeographicLocation $location, ?ContactPerson $contact = null)
    {
        parent::__construct($id);
        $this->setLabel($label);
        $this->setLocation($location);
        $this->setContact($contact);
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
     * @param ContactPerson|null $contact
     */
    public function setContact(?ContactPerson $contact): void
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
        Assert::true($this->matches->isEmpty(), 'Cannot delete pitch which is used in matches');
    }

    /**
     * @param GeographicLocation|null $location
     */
    public function setLocation(?GeographicLocation $location): void
    {
        $this->location = $location;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        Assert::minLength($label, 1, "A pitch's label cannot be blank");
        Assert::maxLength($label, 255, "A pitch's label cannot exceed 255 characters");
        $this->label = $label;
    }
}
