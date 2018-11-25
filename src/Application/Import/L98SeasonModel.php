<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

class L98SeasonModel
{
    /** @var string */
    private $name;

    /** @var L98TeamModel[] */
    private $teams;

    /** @var L98MatchDayModel[] */
    private $matchDays;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->teams = [];
        $this->matchDays = [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return L98TeamModel[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @return L98MatchDayModel[]
     */
    public function getMatchDays(): array
    {
        return $this->matchDays;
    }

    public function addTeam(L98TeamModel $team): void
    {
        $this->teams[] = $team;
    }

    public function addMatchDay(L98MatchDayModel $matchDay): void
    {
        $this->matchDays[] = $matchDay;
    }
}