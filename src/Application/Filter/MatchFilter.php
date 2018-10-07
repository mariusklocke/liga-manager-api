<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Filter;

use HexagonalPlayground\Application\Exception\InvalidInputException;

class MatchFilter
{
    /** @var string|null */
    private $seasonId;

    /** @var string|null */
    private $tournamentId;

    /** @var string|null */
    private $matchDayId;

    /** @var string|null */
    private $teamId;

    /**
     * @param string|null $seasonId
     * @param string|null $tournamentId
     * @param string|null $matchDayId
     * @param string|null $teamId
     */
    public function __construct($seasonId, $tournamentId, $matchDayId, $teamId)
    {
        $this->setSeasonId($seasonId);
        $this->setTournamentId($tournamentId);
        $this->setMatchDayId($matchDayId);
        $this->setTeamId($teamId);
        $this->assertNotAllNull();
    }

    /**
     * @return null|string
     */
    public function getSeasonId(): ?string
    {
        return $this->seasonId;
    }

    /**
     * @param null|string $seasonId
     */
    private function setSeasonId($seasonId): void
    {
        $this->seasonId = $seasonId;
    }

    /**
     * @return null|string
     */
    public function getTournamentId(): ?string
    {
        return $this->tournamentId;
    }

    /**
     * @param null|string $tournamentId
     */
    private function setTournamentId($tournamentId): void
    {
        $this->tournamentId = $tournamentId;
    }

    /**
     * @return null|string
     */
    public function getMatchDayId(): ?string
    {
        return $this->matchDayId;
    }

    /**
     * @param null|string $matchDayId
     */
    private function setMatchDayId($matchDayId): void
    {
        $this->matchDayId = $matchDayId;
    }

    /**
     * @return null|string
     */
    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    /**
     * @param null|string $teamId
     */
    private function setTeamId($teamId): void
    {
        $this->teamId = $teamId;
    }

    /**
     * @throws InvalidInputException
     */
    private function assertNotAllNull(): void
    {
        foreach (get_object_vars($this) as $value) {
            if ($value !== null) {
                return;
            }
        }
        throw new InvalidInputException('MatchFilter requires at least one valid filter value');
    }
}