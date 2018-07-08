<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

class L98MatchModel
{
    /** @var int */
    private $homeTeamId;

    /** @var int */
    private $guestTeamId;

    /** @var int */
    private $homeScore;

    /** @var int */
    private $guestScore;

    /** @var int|null */
    private $kickoff;

    /** @var int */
    private $matchDay;

    /**
     * @param int $homeTeamId
     * @param int $guestTeamId
     * @param int $homeScore
     * @param int $guestScore
     * @param int|null $kickoff
     * @param int $matchDay
     */
    public function __construct(int $homeTeamId, int $guestTeamId, int $homeScore, int $guestScore, ?int $kickoff, int $matchDay)
    {
        $this->homeTeamId  = $homeTeamId;
        $this->guestTeamId = $guestTeamId;
        $this->homeScore   = max($homeScore, 0);
        $this->guestScore  = max($guestScore, 0);
        $this->kickoff     = $kickoff;
        $this->matchDay    = $matchDay;
    }

    /**
     * @return int
     */
    public function getHomeTeamId(): int
    {
        return $this->homeTeamId;
    }

    /**
     * @return int
     */
    public function getGuestTeamId(): int
    {
        return $this->guestTeamId;
    }

    /**
     * @return int
     */
    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    /**
     * @return int
     */
    public function getGuestScore(): int
    {
        return $this->guestScore;
    }

    /**
     * @return int|null
     */
    public function getKickoff(): ?int
    {
        return $this->kickoff;
    }

    /**
     * @return int
     */
    public function getMatchDay(): int
    {
        return $this->matchDay;
    }
}