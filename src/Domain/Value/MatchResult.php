<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;

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
        Assert::true(
            $value >= 0,
            InvalidInputException::class,
            'matchScoreTooLow'
        );
        Assert::true(
            $value <= 99,
            InvalidInputException::class,
            'matchScoreTooHigh'
        );
    }
}
