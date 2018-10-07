<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Filter\MatchFilter;

class MatchRepository extends AbstractRepository
{
    public function findMatches(MatchFilter $filter): array
    {
        $query = 'SELECT m.* FROM `matches` m ';

        if ($filter->getSeasonId() !== null || $filter->getTournamentId() !== null) {
            $query .= 'JOIN `match_days` md ON md.id = m.match_day_id ';
        }

        list($conditionClause, $params) = $this->buildConditionClause($filter);
        if (strlen($conditionClause) > 0 && count($params) > 0) {
            $query .= 'WHERE ' . $conditionClause;
        }

        return $this->getDb()->fetchAll($query, $params);
    }

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
    public function findMatchById(string $matchId)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `matches` WHERE `id` = ?', [$matchId]);
    }
}