<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Domain\User;

class Importer
{
    /** @var TeamMapper */
    private $teamMapper;

    /** @var SeasonMapper */
    private $seasonMapper;

    /** @var MatchMapper */
    private $matchMapper;

    /**
     * @param TeamMapper $teamMapper
     * @param SeasonMapper $seasonMapper
     * @param MatchMapper $matchMapper
     */
    public function __construct(TeamMapper $teamMapper, SeasonMapper $seasonMapper, MatchMapper $matchMapper)
    {
        $this->teamMapper = $teamMapper;
        $this->seasonMapper = $seasonMapper;
        $this->matchMapper = $matchMapper;
    }

    /**
     * @return TeamMapper
     */
    public function getTeamMapper(): TeamMapper
    {
        return $this->teamMapper;
    }

    /**
     * @param L98SeasonModel $l98season
     * @param User $user
     */
    public function import(L98SeasonModel $l98season, User $user)
    {
        $season = $this->seasonMapper->create($l98season);
        foreach ($l98season->getTeams() as $l98Team) {
            $team = $this->teamMapper->getDomainModel($l98Team);
            $season->addTeam($team);
        }
        foreach ($l98season->getMatchDays() as $l98MatchDay) {
            $matchDay = $this->matchMapper->getDomainModel($l98MatchDay, $season);
            $season->addMatchDay($matchDay);
        }
        $season->start();
        foreach ($season->getMatches() as $match) {
            $this->matchMapper->updateMatchDetails($match, $user);
        }
        $season->end();
    }
}