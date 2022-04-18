<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\ContactPerson;
use HexagonalPlayground\Domain\Value\GeographicLocation;

class CreatePitchCommand extends CreateCommand implements CommandInterface
{
    /** @var string */
    private string $label;

    /** @var GeographicLocation */
    private GeographicLocation $location;

    /** @var ContactPerson|null */
    private ?ContactPerson $contact;

    /**
     * @param string $id
     * @param string $label
     * @param GeographicLocation $location
     * @param ContactPerson|null $contact
     */
    public function __construct(string $id, string $label, GeographicLocation $location, ?ContactPerson $contact = null)
    {
        $this->id = $id;
        $this->label = $label;
        $this->location = $location;
        $this->contact = $contact;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return GeographicLocation
     */
    public function getLocation(): GeographicLocation
    {
        return $this->location;
    }

    /**
     * @return ContactPerson|null
     */
    public function getContact(): ?ContactPerson
    {
        return $this->contact;
    }
}
