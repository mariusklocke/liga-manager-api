<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class UpdateTournamentCommand extends UpdateCommand implements CommandInterface
{
    /** @var string */
    private string $name;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
