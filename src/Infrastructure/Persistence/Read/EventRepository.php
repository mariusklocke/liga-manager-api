<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeImmutable;

class EventRepository extends AbstractRepository
{
    /**
     * @param string $id
     * @return array|null
     */
    public function findEventById(string $id): ?array
    {
        $query = $this->getBaseQuery() . ' WHERE id = ?';
        $row   = $this->getDb()->fetchFirstRow($query, [$id]);
        if (null === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @param DateTimeImmutable|null $startDate
     * @param DateTimeImmutable|null $endDate
     * @param string|null $type
     * @return array
     */
    public function findLatestEvents(?DateTimeImmutable $startDate = null, ?DateTimeImmutable $endDate = null, ?string $type = null): array
    {
        $query = $this->getBaseQuery();
        list($conditions, $params) = $this->buildConditions($startDate, $endDate, $type);
        if (strlen($conditions) > 0 && count($params) > 0) {
            $query .= ' WHERE ' . $conditions;
        }
        $query .= ' ORDER BY occurred_at DESC LIMIT 50';
        $result = $this->getDb()->fetchAll($query, $params);
        return array_map([$this, 'hydrate'], $result);
    }

    /**
     * @param DateTimeImmutable|null $startDate
     * @param DateTimeImmutable|null $endDate
     * @param string|null $type
     * @return array
     */
    private function buildConditions(?DateTimeImmutable $startDate = null, ?DateTimeImmutable $endDate = null, ?string $type = null): array
    {
        $conditions = [];
        $parameters = [];
        if ($startDate !== null) {
            $conditions[] = "occurred_at >= ?";
            $parameters[] = $startDate->format(self::MYSQL_DATE_FORMAT);
        }
        if ($endDate !== null) {
            $conditions[] = "occurred_at <= ?";
            $parameters[] = $endDate->format(self::MYSQL_DATE_FORMAT);
        }
        if ($type !== null) {
            $conditions[] = "type = ?";
            $parameters[] = $type;
        }

        return [
            implode(' AND ', $conditions),
            $parameters
        ];
    }

    /**
     * @return string
     */
    private function getBaseQuery(): string
    {
        $occurredAt = $this->getDateFormat('occurred_at');
        return "SELECT id, $occurredAt, payload, type FROM events";
    }

    /**
     * @param array $row
     * @return array
     */
    private function hydrate(array $row): array
    {
        $row['payload'] = unserialize($row['payload']);
        return $row;
    }
}