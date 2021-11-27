<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class AbstractRepository
{
    const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var ReadDbAdapterInterface */
    private $db;

    /** @var Hydrator */
    protected $hydrator;

    public function __construct(ReadDbAdapterInterface $readDbAdapter, Hydrator $hydrator)
    {
        $this->db = $readDbAdapter;
        $this->hydrator = $hydrator;
    }

    /**
     * @return ReadDbAdapterInterface
     */
    protected function getDb(): ReadDbAdapterInterface
    {
        return $this->db;
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
     * @param array $row
     * @return array
     */
    protected function hydrate(array $row): array
    {
        return $row;
    }
}
