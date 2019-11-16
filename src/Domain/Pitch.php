<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Event\PitchContactUpdated;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\ContactPerson;
use HexagonalPlayground\Domain\Value\GeographicLocation;

class Pitch extends Entity
{
    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    /** @var ContactPerson|null */
    private $contact;

    /** @var Collection|Match[] */
    private $matches;

    public function __construct(string $id, string $label, GeographicLocation $location)
    {
        Assert::minLength($label, 1, "A pitch's label cannot be blank");
        Assert::maxLength($label, 255, "A pitch's label cannot exceed 255 characters");
        $this->setId($id);
        $this->label = $label;
        $this->location = $location;
        $this->matches = new ArrayCollection();
    }

    /**
     * @param ContactPerson $contact
     */
    public function setContact(ContactPerson $contact): void
    {
        if (null === $this->contact || !$this->contact->equals($contact)) {
            Publisher::getInstance()->publish(PitchContactUpdated::create($this->id, $this->contact, $contact));
            $this->contact = $contact;
        }
    }

    /**
     * @param Match $match
     */
    public function addMatch(Match $match): void
    {
        if (!$this->matches->containsKey($match->getId())) {
            $this->matches->add($match);
        }
    }

    /**
     * @param Match $match
     */
    public function removeMatch(Match $match): void
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
}
