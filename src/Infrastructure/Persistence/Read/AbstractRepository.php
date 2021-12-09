<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use Iterator;

class AbstractRepository
{
    const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var ReadDbGatewayInterface */
    protected $gateway;

    /** @var Hydrator */
    protected $hydrator;

    public function __construct(ReadDbGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
        $this->hydrator = new Hydrator($this->getFieldDefinitions());
    }

    /**
     * @param Iterator $iterator
     * @return array|null
     */
    protected function hydrateOne(Iterator $iterator): ?array
    {
        foreach ($iterator as $row) {
            return $this->hydrator->hydrate($row);
        }

        return null;
    }

    /**
     * @param Iterator $iterator
     * @param string|null $groupBy
     * @return array
     */
    protected function hydrateMany(Iterator $iterator, ?string $groupBy = null): array
    {
        $result = [];

        foreach ($iterator as $row) {
            $row = $this->hydrator->hydrate($row);

            if ($groupBy !== null) {
                $result[$row[$groupBy]][] = $row;
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getFieldDefinitions(): array
    {
        return [];
    }
}
