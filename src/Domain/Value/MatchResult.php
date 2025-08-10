<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use HexagonalPlayground\Domain\Exception\InvalidInputException;

class MatchResult extends ValueObject
{
    /** @var int */
    protected int $homeScore;

    /** @var int */
    protected int $guestScore;

    public function __construct(int $homeScore, int $guestScore)
    {
        $this->assertValidScoreValue($homeScore);
        $this->assertValidScoreValue($guestScore);
        $this->homeScore  = $homeScore;
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

    /**
     * @param int $value
     */
    private function assertValidScoreValue(int $value): void
    {
        $value >= 0 || throw new InvalidInputException('matchScoreTooLow');
        $value <= 99 || throw new InvalidInputException('matchScoreTooHigh');
    }
}
