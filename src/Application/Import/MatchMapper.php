<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\MatchResult;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\User;

class MatchMapper
{
    /** @var L98MatchModel[] */
    private $identityMap;

    /** @var TeamMapper */
    private $teamMapper;

    /**
     * @param TeamMapper $teamMapper
     */
    public function __construct(TeamMapper $teamMapper)
    {
        $this->teamMapper  = $teamMapper;
        $this->identityMap = [];
    }

    /**
     * @param L98MatchDayModel $matchDay
     * @param Season $season
     * @return MatchDay
     */
    public function getDomainModel(L98MatchDayModel $matchDay, Season $season): MatchDay
    {
        $domainModel = $season->createMatchDay($matchDay->getNumber(), $matchDay->getStartDate(), $matchDay->getEndDate());
        foreach ($matchDay->getMatches() as $l98Match) {
            $domainModel->addMatch($this->createDomainMatch($l98Match, $domainModel));
        }

        return $domainModel;
    }

    private function createDomainMatch(L98MatchModel $l98Match, MatchDay $matchDay): Match
    {
        $domainMatch = new Match(
            $matchDay,
            $this->teamMapper->getDomainModel($l98Match->getHomeTeam()),
            $this->teamMapper->getDomainModel($l98Match->getGuestTeam())
        );
        $this->identityMap[$domainMatch->getId()] = $l98Match;

        return $domainMatch;
    }

    public function updateMatchDetails(Match $match, User $user)
    {
        $l98Match = $this->identityMap[$match->getId()];
        if (null !== $l98Match->getKickoff()) {
            $match->schedule(new \DateTimeImmutable('@' . $l98Match->getKickoff()));
        }
        if ($l98Match->getHomeScore() >= 0 && $l98Match->getGuestScore() >= 0) {
            $match->submitResult(new MatchResult($l98Match->getHomeScore(), $l98Match->getGuestScore()), $user);
        }
    }
}