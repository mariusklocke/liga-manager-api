<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\AbstractRepository;

class MatchLoader extends AbstractRepository
{
    /**
     * @param array $matchDayIds
     * @return array
     */
    public function loadByMatchDayId(array $matchDayIds): array
    {
        if (empty($matchDayIds)) {
            return [];
        }

        $placeholders = $this->getPlaceholders($matchDayIds);
        $kickoff = $this->getDateFormat('kickoff');
        $cancelledAt = $this->getDateFormat('cancelled_at');
        $query = <<<SQL
    SELECT
           id, match_day_id, home_team_id, guest_team_id, pitch_id, $kickoff,
           $cancelledAt, cancellation_reason, home_score, guest_score
    FROM matches
    WHERE match_day_id IN ($placeholders)
SQL;

        $result = [];
        foreach ($this->getDb()->fetchAll($query, $matchDayIds) as $row) {
            $result[$row['match_day_id']][] = $row;
        }

        return $result;
    }
}