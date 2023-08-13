<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;

class RankingPenalty extends Entity
{
    /** @var Ranking */
    private Ranking $ranking;

    /** @var Team */
    private Team $team;

    /** @var string */
    private string $reason;

    /** @var int */
    private int $points;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $createdAt;

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
        Assert::true(
            $points > 0,
            'Points on a RankingPenalty have to be greater than 0',
            InvalidInputException::class
        );
        Assert::true(
            StringUtils::length($reason) > 0,
            'Reason on a RankingPenalty cannot be empty string',
            InvalidInputException::class
        );
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
}
