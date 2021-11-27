<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeImmutable;

class MatchRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'match_day_id' => Hydrator::TYPE_STRING,
            'home_team_id' => Hydrator::TYPE_STRING,
            'guest_team_id' => Hydrator::TYPE_STRING,
            'pitch_id' => Hydrator::TYPE_STRING,
            'kickoff' => Hydrator::TYPE_DATETIME,
            'cancelled_at' => Hydrator::TYPE_DATETIME,
            'cancellation_reason' => Hydrator::TYPE_STRING,
            'home_score' => Hydrator::TYPE_INT,
            'guest_score' => Hydrator::TYPE_INT
        ];
    }

    /**
     * @param string $matchId
     * @return array|null
     */
    public function findMatchById(string $matchId): ?array
    {
        $row = $this->getDb()->fetchFirstRow($this->getBaseQuery() . ' WHERE id = ?', [$matchId]);

        return $row !== null ? $this->hydrateOne($row) : null;
    }

    /**
     * @param array $matchDayIds
     * @return array
     */
    public function findMatchesByMatchDayIds(array $matchDayIds): array
    {
        if (empty($matchDayIds)) {
            return [];
        }

        $placeholders = $this->getPlaceholders($matchDayIds);
        $query = <<<SQL
    SELECT *
    FROM matches
    WHERE match_day_id IN ($placeholders)
SQL;

        return $this->hydrateMany($this->getDb()->fetchAll($query, $matchDayIds), 'match_day_id');
    }

    /**
     * @param DateTimeImmutable|null $minDate
     * @param DateTimeImmutable|null $maxDate
     * @return array
     */
    public function findMatchesByKickoff(?DateTimeImmutable $minDate, ?DateTimeImmutable $maxDate): array
    {
        $query = $this->getBaseQuery();

        $conditions = [];
        $parameters = [];

        if ($minDate !== null) {
            $conditions[] = "kickoff >= ?";
            $parameters[] = $minDate->format(self::MYSQL_DATE_FORMAT);
        }

        if ($maxDate !== null) {
            $conditions[] = "kickoff <= ?";
            $parameters[] = $maxDate->format(self::MYSQL_DATE_FORMAT);
        }

        if (count($conditions) && count($parameters)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY kickoff ASC';

        return $this->hydrateMany($this->getDb()->fetchAll($query, $parameters));
    }

    /**
     * @return string
     */
    private function getBaseQuery(): string
    {
        return <<<SQL
  SELECT * FROM matches m
SQL;
    }
}
