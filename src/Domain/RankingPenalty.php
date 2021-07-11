<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Util\Assert;

class RankingPenalty extends Entity
{
    /** @var Ranking */
    private $ranking;

    /** @var Team */
    private $team;

    /** @var string */
    private $reason;

    /** @var int */
    private $points;

    /** @var DateTimeImmutable */
    private $createdAt;

    /**
     * @param string $id
     * @param Ranking $ranking
     * @param Team $team
     * @param string $reason
     * @param int $points
     */
    public function __construct(string $id, Ranking $ranking, Team $team, string $reason, int $points)
    {
        parent::__construct($id);
        Assert::true($points > 0, 'Points on a RankingPenalty have to be greater than 0');
        Assert::minLength($reason, 1, 'Reason on a RankingPenalty cannot be empty string');

        $this->ranking   = $ranking;
        $this->team      = $team;
        $this->reason    = $reason;
        $this->points    = $points;
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }
}