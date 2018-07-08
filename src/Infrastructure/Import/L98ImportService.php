<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use Generator;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;
use HexagonalPlayground\Domain\MatchResult;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class L98ImportService
{
    /** @var L98FileParser */
    private $parser;

    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var array */
    private $identityMap;

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
    }

    public function init(string $filepath)
    {
        $this->parser = new L98FileParser($filepath);
        $this->identityMap = [];
    }

    /**
     * @return Generator|L98TeamModel[]
     */
    public function getImportableTeams(): Generator
    {
        return $this->parser->getTeams();
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

        return $teams;
    }

    public function addTeamMapping(L98TeamModel $importableTeam, Team $domainTeam)
    {
        $this->identityMap[$importableTeam->getId()] = $domainTeam->getId();
    }

    public function import(User $user)
    {
        $season = new Season($this->parser->getSeason()->getName());
        $this->seasonRepository->save($season);

        $matchFactory = new MatchFactory();
        foreach ($this->getImportableTeams() as $importableTeam) {
            if (!isset($this->identityMap[$importableTeam->getId()])) {
                $domainTeam = new Team($importableTeam->getName());
                $this->teamRepository->save($domainTeam);
                $this->identityMap[$importableTeam->getId()] = $domainTeam->getId();
            } else {
                $domainTeam = $this->teamRepository->find($this->identityMap[$importableTeam->getId()]);
            }
            $season->addTeam($domainTeam);
        }

        /** @var L98MatchModel[] $matchMap */
        $matchMap = [];
        foreach ($this->parser->getMatches() as $importableMatch) {
            $homeTeam = $this->teamRepository->find($this->identityMap[$importableMatch->getHomeTeamId()]);
            $guestTeam = $this->teamRepository->find($this->identityMap[$importableMatch->getGuestTeamId()]);
            $match = $matchFactory->createMatch($season, $importableMatch->getMatchDay(), $homeTeam, $guestTeam);
            $this->matchRepository->save($match);
            $season->addMatch($match);
            $matchMap[$match->getId()] = $importableMatch;
        }
        $season->start();

        foreach ($season->getMatches() as $match) {
            $importableMatch = $matchMap[$match->getId()];
            if (null !== $importableMatch->getKickoff()) {
                $match->schedule(new \DateTimeImmutable('@' . $importableMatch->getKickoff()));
            }
            $match->submitResult(new MatchResult($importableMatch->getHomeScore(), $importableMatch->getGuestScore()), $user);
        }
        $season->end();
    }
}