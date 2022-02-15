<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

class Hydrator
{
    /** @var array|Field[] */
    private array $fields;

    /**
     * @param iterable|Field[] $fields
     */
    public function __construct(iterable $fields)
    {
        $this->fields = [];

        foreach ($fields as $field) {
            $this->fields[$field->getName()] = $field;
        }
    }

    /**
     * @param iterable|array[] $rows
     * @return array|null
     */
    public function hydrateOne(iterable $rows): ?array
    {
        foreach ($rows as $row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * @param iterable|array[] $rows
     * @param string|null $groupBy
     * @return array
     */
    public function hydrateMany(iterable $rows, ?string $groupBy = null): array
    {
        $result = [];

        foreach ($rows as $row) {
            if ($groupBy !== null) {
                $result[$row[$groupBy]][] = $this->hydrate($row);
            } else {
                $result[] = $this->hydrate($row);
            }
        }

        return $result;
    }

    /**
     * Converts a database row to API-compatible format
     *
     * @param array $row
     * @return array
     */
    private function hydrate(array $row): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            $result[$field->getName()] = $field->hydrate($row);
        }

        return $result;
    }
}
