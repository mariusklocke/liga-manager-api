<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class Pitch
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    /** @var ContactPerson */
    private $contact;

    public function __construct(string $label, GeographicLocation $location)
    {
        $this->id = Uuid::create();
        $this->label = $label;
        $this->location = $location;
    }

    public function copy(string $id)
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param ContactPerson $contact
     */
    public function setContact(ContactPerson $contact): void
    {
        $this->contact = $contact;
    }

    private function __clone()
    {
        $this->id = null;
    }
}
