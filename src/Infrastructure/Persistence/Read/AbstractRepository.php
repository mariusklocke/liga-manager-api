<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
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
        return $this->hydrator->hydrateMany($this->gateway->fetch(
            $this->getTableName(),
            [],
            $filters,
            $sortings,
            $pagination
        ), $groupBy);
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        return $this->hydrator->hydrateOne($this->gateway->fetch(
            $this->getTableName(),
            [],
            [new EqualityFilter($this->getField('id'), Filter::MODE_INCLUDE, [$id])]
        ));
    }

    /**
     * @param string $name
     * @return Field|null
     */
    public function getField(string $name): ?Field
    {
        foreach ($this->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->getName() === $name) {
                return $fieldDefinition;
            }
        }

        return null;
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
