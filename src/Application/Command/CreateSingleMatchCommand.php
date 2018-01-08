<?php

namespace HexagonalDream\Application\Command;

class CreateSingleMatchCommand implements CommandInterface
{
    /** @var string */
    private $seasonId;

    /** @var int */
    private $matchDay;

    /** @var string */
    private $homeTeamId;

    /** @var string */
    private $guestTeamId;

    /**
     * @param string $seasonId
     * @param int $matchDay
     * @param string $homeTeamId
     * @param string $guestTeamId
     */
    public function __construct(string $seasonId, int $matchDay, string $homeTeamId, string $guestTeamId)
    {
        $this->seasonId = $seasonId;
        $this->matchDay = $matchDay;
        $this->homeTeamId = $homeTeamId;
        $this->guestTeamId = $guestTeamId;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return int
     */
    public function getMatchDay(): int
    {
        return $this->matchDay;
    }

    /**
     * @return string
     */
    public function getHomeTeamId(): string
    {
        return $this->homeTeamId;
    }

    /**
     * @return string
     */
    public function getGuestTeamId(): string
    {
        return $this->guestTeamId;
    }
}
