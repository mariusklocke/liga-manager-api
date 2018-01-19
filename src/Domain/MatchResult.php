<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class MatchResult
{
    /** @var int */
    private $homeScore;

    /** @var int */
    private $guestScore;

    public function __construct(int $homeScore, int $guestScore)
    {
        $this->homeScore = $homeScore;
        $this->guestScore = $guestScore;
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
