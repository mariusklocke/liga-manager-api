<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class SubmitMatchResultCommand implements CommandInterface
{
    /** @var string */
    private string $matchId;
    /** @var int */
    private int $homeScore;
    /** @var int */
    private int $guestScore;

    /**
     * @param string $matchId
     * @param int $homeScore
     * @param int $guestScore
     */
    public function __construct(string $matchId, int $homeScore, int $guestScore)
    {
        $this->matchId = $matchId;
        $this->homeScore = $homeScore;
        $this->guestScore = $guestScore;
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->matchId;
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
}
