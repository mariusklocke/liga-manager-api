<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Filter\EventFilter;

class EventRepository extends AbstractRepository
{
    /**
     * @param string $id
     * @return array
     */
    public function findEventById(string $id): array
    {
        $query = $this->getBaseQuery() . ' WHERE id = ?';
        $row   = $this->getDb()->fetchFirstRow($query, [$id]);
        if (null === $row) {
            throw new NotFoundException('Cannot find event');
        }
        return $this->hydrate($row);
    }

    /**
     * @param EventFilter $filter
     * @return array
     */
    public function findLatestEvents(EventFilter $filter): array
    {
        $query = $this->getBaseQuery();
        list($conditions, $params) = $this->buildConditions($filter);
        if (strlen($conditions) > 0 && count($params) > 0) {
            $query .= ' WHERE ' . $conditions;
        }
        $query .= ' ORDER BY occurred_at DESC LIMIT 50';
        $result = $this->getDb()->fetchAll($query, $params);
        return array_map([$this, 'hydrate'], $result);
    }

    /**
     * @param EventFilter $filter
     * @return array
     */
    private function buildConditions(EventFilter $filter): array
    {
        $conditions = [];
        $parameters = [];
        if ($filter->getStartDate() !== null) {
            $conditions[] = "occurred_at >= ?";
            $parameters[] = $filter->getStartDate()->format(self::MYSQL_DATE_FORMAT);
        }
        if ($filter->getEndDate() !== null) {
            $conditions[] = "occurred_at <= ?";
            $parameters[] = $filter->getEndDate()->format(self::MYSQL_DATE_FORMAT);
        }
        if ($filter->getType() !== null) {
            $conditions[] = "type = ?";
            $parameters[] = $filter->getType();
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