<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;

class RankingPenalty
{
    /** @var string */
    private $id;

    /** @var Ranking */
    private $ranking;

    /** @var Team */
    private $team;

    /** @var string */
    private $reason;

    /** @var int */
    private $points;

    /** @var \DateTimeImmutable */
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
        Assert::minLength($id, 1, "A RankingPenalty's id cannot be blank");
        Assert::true($points > 0, 'Points on a RankingPenalty have to be greater than 0');
        Assert::minLength($reason, 1, 'Reason on a RankingPenalty cannot be empty string');
        $this->id        = $id;
        $this->ranking   = $ranking;
        $this->team      = $team;
        $this->reason    = $reason;
        $this->points    = $points;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Ranking
     */
    public function getRanking(): Ranking
    {
        return $this->ranking;
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
}