<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class RemoveRankingPenaltyCommand implements CommandInterface
{
    /** @var string */
    private string $rankingPenaltyId;

    /** @var string */
    private string $seasonId;

    /**
     * @param string $rankingPenaltyId
     * @param string $seasonId
     */
    public function __construct(string $rankingPenaltyId, string $seasonId)
    {
        $this->rankingPenaltyId = $rankingPenaltyId;
        $this->seasonId = $seasonId;
    }

    /**
     * @return string
     */
    public function getRankingPenaltyId(): string
    {
        return $this->rankingPenaltyId;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }
}