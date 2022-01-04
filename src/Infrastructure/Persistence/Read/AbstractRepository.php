<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

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
     * @param string|null $groupBy
     * @return array
     */
    public function findMany(
        iterable    $filters = [],
        iterable    $sortings = [],
        ?Pagination $pagination = null,
        ?string     $groupBy = null
    ): array {
        foreach ($filters as $filter) {
            $fieldDefinition = current(array_filter($this->getFieldDefinitions(), function (Field $field) use ($filter): bool {
                return $field->getName() === $filter->getField();
            }));

            $filter->validate($fieldDefinition);
        }

        return $this->hydrator->hydrateMany($this->gateway->fetch(
            $this->getTableName(),
            [],
            $filters,
            $sortings,
            $pagination
        ), $groupBy);
    }

    /**
     * @return array|Field[]
     */
    abstract protected function getFieldDefinitions(): array;

    /**
     * @return string
     */
    abstract protected function getTableName(): string;
}
