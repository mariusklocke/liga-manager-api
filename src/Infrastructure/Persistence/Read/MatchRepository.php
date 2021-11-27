<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeImmutable;

class MatchRepository extends AbstractRepository
{
    /**
     * @param string $matchId
     * @return array|null
     */
    public function findMatchById(string $matchId): ?array
    {
        $row = $this->getDb()->fetchFirstRow($this->getBaseQuery() . ' WHERE id = ?', [$matchId]);

        return $row !== null ? $this->hydrate($row) : null;
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

        $result = [];
        foreach ($this->getDb()->fetchAll($query, $matchDayIds) as $row) {
            $result[$row['match_day_id']][] = $this->hydrate($row);
        }

        return $result;
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

        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll($query, $parameters));
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

    protected function hydrate(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'match_day_id' => $this->hydrator->string($row['match_day_id']),
            'home_team_id' => $this->hydrator->string($row['home_team_id']),
            'guest_team_id' => $this->hydrator->string($row['guest_team_id']),
            'pitch_id' => $this->hydrator->string($row['pitch_id']),
            'kickoff' => $this->hydrator->dateTime($row['kickoff']),
            'cancelled_at' => $this->hydrator->dateTime($row['cancelled_at']),
            'cancellation_reason' => $this->hydrator->string($row['cancellation_reason']),
            'home_score' => $this->hydrator->int($row['home_score']),
            'guest_score' => $this->hydrator->int($row['guest_score']),
        ];
    }
}
