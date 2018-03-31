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
        $this->assertValidScoreValue($homeScore);
        $this->assertValidScoreValue($guestScore);
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

    /**
     * @param int $value
     * @throws DomainException
     */
    private function assertValidScoreValue(int $value)
    {
        if ($value < 0 || $value > 99) {
            throw new DomainException('Match scores have to be integer values between 0 and 99');
        }
    }
}
