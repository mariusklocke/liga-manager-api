<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Filter\MatchFilter;

class MatchRepository extends AbstractRepository
{
    /**
     * @param MatchFilter $filter
     * @return array
     */
    public function findMatches(MatchFilter $filter): array
    {
        $query = $this->getBaseQuery() . ' JOIN match_days md ON md.id = m.match_day_id';
        list($conditionClause, $params) = $this->buildConditionClause($filter);
        if (strlen($conditionClause) > 0 && count($params) > 0) {
            $query .= ' WHERE ' . $conditionClause;
        }
        $query .= ' ORDER BY md.number ASC';

        return $this->getDb()->fetchAll($query, $params);
    }

    /**
     * @param MatchFilter $filter
     * @return array
     */
    private function buildConditionClause(MatchFilter $filter)
    {
        $conditions = [];
        $parameters = [];
        if ($filter->getSeasonId() !== null) {
            $conditions[] = "md.season_id = ?";
            $parameters[] = $filter->getSeasonId();
        }
        if ($filter->getTournamentId() !== null) {
            $conditions[] = "md.tournament_id = ?";
            $parameters[] = $filter->getTournamentId();
        }
        if ($filter->getMatchDayId() !== null) {
            $conditions[] = "m.match_day_id = ?";
            $parameters[] = $filter->getMatchDayId();
        }
        if ($filter->getTeamId() !== null) {
            $conditions[] = "(m.home_team_id = ? OR m.guest_team_id = ?)";
            $parameters[] = $filter->getTeamId();
            $parameters[] = $filter->getTeamId();
        }

        return [
            implode(' AND ', $conditions),
            $parameters
        ];
    }

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