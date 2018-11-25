<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

class L98MatchModel
{
    /** @var L98TeamModel */
    private $homeTeam;

    /** @var L98TeamModel */
    private $guestTeam;

    /** @var int */
    private $homeScore;

    /** @var int */
    private $guestScore;

    /** @var int|null */
    private $kickoff;

    /** @var int */
    private $matchDay;

    /**
     * @param L98TeamModel $homeTeam
     * @param L98TeamModel $guestTeam
     * @param int $homeScore
     * @param int $guestScore
     * @param int|null $kickoff
     * @param int $matchDay
     */
    public function __construct(L98TeamModel $homeTeam, L98TeamModel $guestTeam, int $homeScore, int $guestScore, ?int $kickoff, int $matchDay)
    {
        $this->homeTeam    = $homeTeam;
        $this->guestTeam   = $guestTeam;
        $this->homeScore   = $homeScore;
        $this->guestScore  = $guestScore;
        $this->kickoff     = $kickoff;
        $this->matchDay    = $matchDay;
    }

    /**
     * @return L98TeamModel
     */
    public function getHomeTeam(): L98TeamModel
    {
        return $this->homeTeam;
    }

    /**
     * @return L98TeamModel
     */
    public function getGuestTeam(): L98TeamModel
    {
        return $this->guestTeam;
    }

    /**
     * @return int
     */
    public function getHomeTeamId(): int
    {
        return $this->homeTeam->getId();
    }

    /**
     * @return int
     */
    public function getGuestTeamId(): int
    {
        return $this->guestTeam->getId();
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