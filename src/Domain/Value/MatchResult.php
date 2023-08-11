<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

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
    private function assertValidScoreValue(int $value)
    {
        // TODO: This should become InvalidInputException
        Assert::true($value >= 0, 'Match scores have to be greater or equal than 0');
        // TODO: This should become InvalidInputException
        Assert::true($value <= 99, 'Match scores have to be less or equal than 99');
    }
}
