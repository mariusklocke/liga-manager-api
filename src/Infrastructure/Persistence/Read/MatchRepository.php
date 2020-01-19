<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class MatchRepository extends AbstractRepository
{
    /**
     * @param string $matchId
     * @return array|null
     */
    public function findMatchById(string $matchId): ?array
    {
        return $this->getDb()->fetchFirstRow($this->getBaseQuery() . ' WHERE m.id = ?', [$matchId]);
    }

    /**
     * @return string
     */
    private function getBaseQuery(): string
    {
        $kickoff     = $this->getDateFormat('m.kickoff','kickoff');
        $cancelledAt = $this->getDateFormat('m.cancelled_at', 'cancelled_at');

        return <<<SQL
  SELECT m.id, m.match_day_id, m.home_team_id, m.guest_team_id, m.pitch_id, $kickoff, $cancelledAt,
         m.cancellation_reason, m.home_score, m.guest_score
  FROM matches m
SQL;
    }
}