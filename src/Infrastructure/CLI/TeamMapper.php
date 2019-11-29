<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Import\TeamMapperInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use Symfony\Component\Console\Style\StyleInterface;

class TeamMapper implements TeamMapperInterface
{
    /** @var StyleInterface|null */
    private $styledIo;

    /** @var TeamRepositoryInterface */
    private $repository;

    /** @var array */
    private $mapping;

    /**
     * @param TeamRepositoryInterface $repository
     */
    public function __construct(TeamRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->mapping = [];
    }

    /**
     * @param StyleInterface $styledIo
     */
    public function setStyledIo(StyleInterface $styledIo): void
    {
        $this->styledIo = $styledIo;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function map(string $name): ?string
    {
        if (!isset($this->mapping[$name])) {
            $teams = $this->getRecommendations($name);
            if (!empty($teams)) {
                $this->mapping[$name] = $this->askUserToSelectTeam($name, $teams);
            } else {
                $this->mapping[$name] = null;
            }
        }
        return $this->mapping[$name];
    }

    /**
     * @param string $importName
     * @param array $recommendedTeams
     * @return string|null
     */
    private function askUserToSelectTeam(string $importName, array $recommendedTeams): ?string
    {
        if (null === $this->styledIo) {
            return null;
        }

        $choices = [];
        foreach ($recommendedTeams as $index => $recommendedTeam) {
            $choices[$index] = $recommendedTeam->getName();
        }
        $choices['*'] = '--- Import as a new team ---';
        $answer = $this->styledIo->choice('Please choose how to map ' . $importName, $choices);
        if (isset($recommendedTeams[$answer])) {
            return $recommendedTeams[$answer];
        } else {
            $selectedTeamIndex = array_search($answer, $choices);
            if ($selectedTeamIndex !== false && isset($recommendedTeams[$selectedTeamIndex])) {
                return $recommendedTeams[$answer];
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return Team[]
     */
    private function getRecommendations(string $name): array
    {
        $teams = $this->repository->findAll();
        uasort($teams, function(Team $t1, Team $t2) use ($name) {
            return levenshtein($t1->getName(), $name) <=> levenshtein($t2->getName(), $name);
        });

        return array_slice($teams, 0, 5);
    }
}
