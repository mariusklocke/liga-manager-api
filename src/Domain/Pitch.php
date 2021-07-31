<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Event\Event;
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

    /** @var Collection|MatchEntity[] */
    private $matches;

    public function __construct(string $id, string $label, GeographicLocation $location)
    {
        parent::__construct($id);
        Assert::minLength($label, 1, "A pitch's label cannot be blank");
        Assert::maxLength($label, 255, "A pitch's label cannot exceed 255 characters");
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
            Publisher::getInstance()->publish(new Event('pitch:contact:updated', [
                'pitchId' => $this->id,
                'oldContact' => $this->contact !== null ? $this->contact->toArray() : null,
                'newContact' => $contact->toArray()
            ]));
            $this->contact = $contact;
        }
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
}
