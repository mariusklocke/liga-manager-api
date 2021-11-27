<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class AbstractRepository
{
    const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var ReadDbAdapterInterface */
    private $db;

    /** @var Hydrator */
    private $hydrator;

    public function __construct(ReadDbAdapterInterface $readDbAdapter)
    {
        $this->db = $readDbAdapter;
        $this->hydrator = new Hydrator($this->getFieldDefinitions());
    }

    /**
     * @return ReadDbAdapterInterface
     */
    protected function getDb(): ReadDbAdapterInterface
    {
        return $this->db;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function hydrateOne(array $row): array
    {
        return $this->hydrator->hydrate($row);
    }

    /**
     * @param array $rows
     * @param string|null $groupBy
     * @return array
     */
    protected function hydrateMany(array $rows, ?string $groupBy = null): array
    {
        $result = [];

        foreach ($rows as $row) {
            $row = $this->hydrateOne($row);

            if ($groupBy !== null) {
                $result[$row[$groupBy]][] = $row;
                continue;
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function getPlaceholders(array $params): string
    {
        return implode(',', array_fill(0, count($params), '?'));
    }

    /**
     * @return array
     */
    protected function getFieldDefinitions(): array
    {
        return [];
    }
}
