<?php

namespace HexagonalPlayground\Application\Command;

class SubmitMatchResultCommand implements CommandInterface
{
    /** @var string */
    private $matchId;
    /** @var int */
    private $homeScore;
    /** @var int */
    private $guestScore;

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
