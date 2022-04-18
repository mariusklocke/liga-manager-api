<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class CreateSeasonCommand extends CreateCommand implements CommandInterface
{
    /** @var string */
    private string $name;

    /** @var array|string[] */
    private array $teamIds;

    /**
     * @param string $id
     * @param string      $name
     * @param string[]    $teamIds
     */
    public function __construct(string $id, string $name, array $teamIds)
    {
        $this->id = $id;
        $this->name = $name;
        $this->teamIds = $teamIds;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array|string[]
     */
    public function getTeamIds(): array
    {
        return $this->teamIds;
    }
}
