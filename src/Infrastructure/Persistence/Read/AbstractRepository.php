<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;

abstract class AbstractRepository
{
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
     * @param iterable|Filter[] $filters
     * @param iterable|Sorting[] $sortings
     * @param Pagination|null $pagination
     * @return array
     */
    public function findMany(
        iterable    $filters = [],
        iterable    $sortings = [],
        ?Pagination $pagination = null
    ): array {
        foreach ($filters as $filter) {
            $filter->validate($this->getFieldDefinitions());
        }

        return $this->hydrator->hydrateMany($this->gateway->fetch(
            $this->getTableName(),
            [],
            $filters,
            $sortings,
            $pagination
        ));
    }

    /**
     * @return array
     */
    abstract protected function getFieldDefinitions(): array;

    /**
     * @return string
     */
    abstract protected function getTableName(): string;
}
