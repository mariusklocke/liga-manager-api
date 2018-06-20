<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\User;

class SubmitMatchResultCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchId;
    /** @var int */
    private $homeScore;
    /** @var int */
    private $guestScore;

    public function __construct(string $matchId, int $homeScore, int $guestScore, User $authenticatedUser)
    {
        $this->matchId = $matchId;
        $this->homeScore = $homeScore;
        $this->guestScore = $guestScore;
        $this->authenticatedUser = $authenticatedUser;
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
