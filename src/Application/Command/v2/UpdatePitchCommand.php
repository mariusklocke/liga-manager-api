<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\ContactPerson;
use HexagonalPlayground\Domain\Value\GeographicLocation;

class UpdatePitchCommand extends UpdateCommand implements CommandInterface
{
    private string $label;

    private ?GeographicLocation $location;

    private ?ContactPerson $contact;

    /**
     * @param string $label
     * @param GeographicLocation|null $location
     * @param ContactPerson|null $contact
     */
    public function __construct(string $label, ?GeographicLocation $location, ?ContactPerson $contact)
    {
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
     * @return GeographicLocation|null
     */
    public function getLocation(): ?GeographicLocation
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
