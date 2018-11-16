<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\MatchResult;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class L98ImportService
{
    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var array */
    private $teamIdentityMap;

    /**
     * @param MatchRepositoryInterface $matchRepository
     * @param TeamRepositoryInterface $teamRepository
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(
        MatchRepositoryInterface $matchRepository,
        TeamRepositoryInterface $teamRepository,
        SeasonRepositoryInterface $seasonRepository
    ) {
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
        $this->seasonRepository = $seasonRepository;
        $this->teamIdentityMap = [];
    }

    /**
     * @param L98TeamModel $importableTeam
     * @return Team[]
     */
    public function getTeamMappingRecommendations(L98TeamModel $importableTeam): array
    {
        $teams = $this->teamRepository->findAll();
        uasort($teams, function(Team $t1, Team $t2) use ($importableTeam) {
            return levenshtein($t1->getName(), $importableTeam->getName()) <=> levenshtein($t2->getName(), $importableTeam->getName());
        });

        return array_slice($teams, 0, 5);
    }

    public function addTeamMapping(L98TeamModel $importableTeam, Team $domainTeam): void
    {
        $this->teamIdentityMap[$importableTeam->getId()] = $domainTeam->getId();
    }

    /**
     * @param L98SeasonModel $importableSeason
     * @param L98TeamModel[] $importableTeams
     * @param L98MatchDayModel[] $importableMatchDays
     * @param User $user
     */
    public function import(L98SeasonModel $importableSeason, array $importableTeams, array $importableMatchDays, User $user): void
    {
        $season = $this->importSeason($importableSeason);
        $this->importTeams($season, $importableTeams);
        $this->importMatches($season, $importableMatchDays, $user);
    }

    /**
     * @param L98SeasonModel $importableSeason
     * @return Season
     */
    private function importSeason(L98SeasonModel $importableSeason): Season
    {
        $season = new Season($importableSeason->getName());
        $this->seasonRepository->save($season);

        return $season;
    }

    /**
     * @param Season $season
     * @param L98TeamModel[] $importableTeams
     */
    private function importTeams(Season $season, array $importableTeams)
    {
        foreach ($importableTeams as $importableTeam) {
            if (!isset($this->teamIdentityMap[$importableTeam->getId()])) {
                $domainTeam = new Team($importableTeam->getName());
                $this->teamRepository->save($domainTeam);
                $this->teamIdentityMap[$importableTeam->getId()] = $domainTeam->getId();
            } else {
                $domainTeam = $this->teamRepository->find($this->teamIdentityMap[$importableTeam->getId()]);
            }
            $season->addTeam($domainTeam);
        }
    }

    /**
     * @param Season $season
     * @param L98MatchDayModel[] $matchDays
     * @param User $user
     */
    private function importMatches(Season $season, array $matchDays, User $user)
    {
        /** @var L98MatchModel[] $matchMap */
        $matchMap = $this->createMatches($season, $matchDays);
        $season->start();
        $this->updateMatchDetails($season, $matchMap, $user);
        $season->end();
    }

    /**
     * @param Season $season
     * @param L98MatchDayModel[] $importableMatchDays
     * @return array
     */
    private function createMatches(Season $season, array $importableMatchDays): array
    {
        $matchMap = [];

        foreach ($importableMatchDays as $importableMatchDay) {
            $matchDay = new MatchDay(
                $season,
                $importableMatchDay->getNumber(),
                $importableMatchDay->getStartDate(),
                $importableMatchDay->getEndDate()
            );

            foreach ($importableMatchDay->getMatches() as $importableMatch) {
                /** @var L98MatchModel $importableMatch */
                $internalHomeTeamId  = $this->teamIdentityMap[$importableMatch->getHomeTeamId()] ?? null;
                $internalGuestTeamId = $this->teamIdentityMap[$importableMatch->getGuestTeamId()] ?? null;
                if (null === $internalHomeTeamId || null === $internalGuestTeamId) {
                    continue;
                }

                $homeTeam  = $this->teamRepository->find($internalHomeTeamId);
                $guestTeam = $this->teamRepository->find($internalGuestTeamId);
                $match = new Match($matchDay, $homeTeam, $guestTeam);
                $this->matchRepository->save($match);
                $matchDay->addMatch($match);
                $matchMap[$match->getId()] = $importableMatch;
            }

            $season->addMatchDay($matchDay);
        }

        return $matchMap;
    }

    /**
     * @param Season $season
     * @param array $matchMap
     * @param User $user
     */
    private function updateMatchDetails(Season $season, array $matchMap, User $user)
    {
        foreach ($season->getMatches() as $match) {
            $importableMatch = $matchMap[$match->getId()];
            if (null !== $importableMatch->getKickoff()) {
                $match->schedule(new \DateTimeImmutable('@' . $importableMatch->getKickoff()));
            }
            if ($importableMatch->getHomeScore() >= 0 && $importableMatch->getGuestScore() >= 0) {
                $match->submitResult(new MatchResult($importableMatch->getHomeScore(), $importableMatch->getGuestScore()), $user);
            }
        }
    }

}