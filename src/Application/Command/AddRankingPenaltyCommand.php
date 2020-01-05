<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class AddRankingPenaltyCommand implements CommandInterface
{
    use IdAware;

    /** @var string */
    private $seasonId;

    /** @var string */
    private $teamId;

    /** @var string */
    private $reason;

    /** @var int */
    private $points;

    /**
     * @param string|null $id
     * @param string      $seasonId
     * @param string      $teamId
     * @param string      $reason
     * @param int         $points
     */
    public function __construct($id, $seasonId, $teamId, $reason, $points)
    {
        TypeAssert::assertString($seasonId, 'seasonId');
        TypeAssert::assertString($teamId, 'teamId');
        TypeAssert::assertString($reason, 'reason');
        TypeAssert::assertInteger($points, 'points');
        $this->seasonId = $seasonId;
        $this->teamId = $teamId;
        $this->reason = $reason;
        $this->points = $points;
        $this->setId($id);
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
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