<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class SubmitMatchResultCommand implements CommandInterface
{
    /** @var string */
    private $matchId;
    /** @var int */
    private $homeScore;
    /** @var int */
    private $guestScore;

    /**
     * @param string $matchId
     * @param int $homeScore
     * @param int $guestScore
     */
    public function __construct($matchId, $homeScore, $guestScore)
    {
        TypeAssert::assertString($matchId, 'matchId');
        TypeAssert::assertInteger($homeScore, 'homeScore');
        TypeAssert::assertInteger($guestScore, 'guestScore');
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
