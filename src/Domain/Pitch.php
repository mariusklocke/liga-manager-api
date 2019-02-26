<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\Uuid;

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

    public function __construct(string $id, string $label, GeographicLocation $location)
    {
        Assert::minLength($id, 1, "A pitch's id cannot be blank");
        Assert::minLength($label, 1, "A pitch's label cannot be blank");
        Assert::maxLength($label, 255, "A pitch's label cannot exceed 255 characters");
        $this->id = $id;
        $this->label = $label;
        $this->location = $location;
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
}
