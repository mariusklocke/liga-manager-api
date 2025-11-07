<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class SubmitMatchResultCommand implements CommandInterface
{
    /** @var string */
    private string $matchId;
    /** @var int|null */
    private ?int $homeScore;
    /** @var int|null */
    private ?int $guestScore;

    /**
     * @param string $matchId
     * @param int|null $homeScore
     * @param int|null $guestScore
     */
    public function __construct(string $matchId, ?int $homeScore, ?int $guestScore)
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
     * @return int|null
     */
    public function getHomeScore(): ?int
    {
        return $this->homeScore;
    }

    /**
     * @return int|null
     */
    public function getGuestScore(): ?int
    {
        return $this->guestScore;
    }
}
