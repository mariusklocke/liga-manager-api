<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class UpdateSeasonCommand extends UpdateCommand implements CommandInterface
{
    /** @var string */
    private string $name;

    /** @var array|string[] */
    private array $teamIds;

    /** @var string */
    private string $state;

    /**
     * @param string $id
     * @param string $name
     * @param array|string[] $teamIds
     * @param string $state
     */
    public function __construct(string $id, string $name, array $teamIds, string $state)
    {
        $this->id = $id;
        $this->name = $name;
        $this->teamIds = $teamIds;
        $this->state = $state;
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

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }
}
