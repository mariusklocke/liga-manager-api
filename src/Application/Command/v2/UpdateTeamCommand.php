<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\ContactPerson;

class UpdateTeamCommand extends UpdateCommand implements CommandInterface
{
    /** @var string */
    private string $name;

    /** @var ContactPerson|null */
    private ?ContactPerson $contact;

    /**
     * @param string $id
     * @param string $name
     * @param ContactPerson|null $contact
     */
    public function __construct(string $id, string $name, ?ContactPerson $contact = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->contact = $contact;
    }

    /**
     * @return string
     */
    public function getName(): string
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
}
