<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\EmbeddedObjectField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

abstract class AbstractRepository
{
    /** @var ReadDbGatewayInterface */
    protected $gateway;

    /** @var Hydrator */
    protected $hydrator;

    /** @var array|Field[] */
    protected $flattenedFieldDefinitions;

    public function __construct(ReadDbGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
        $this->hydrator = new Hydrator($this->getFieldDefinitions());
        $this->flattenFieldDefinitions();
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
        return $this->flattenedFieldDefinitions[$name] ?? null;
    }

    protected function flattenFieldDefinitions(): void
    {
        $this->flattenedFieldDefinitions = [];

        foreach ($this->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition instanceof EmbeddedObjectField) {
                foreach ($fieldDefinition->getSubFields() as $subField) {
                    $subField = $subField->withName($fieldDefinition->getName() . '_' . $subField->getName());
                    $this->flattenedFieldDefinitions[$subField->getName()] = $subField;
                }
                continue;
            }

            $this->flattenedFieldDefinitions[$fieldDefinition->getName()] = $fieldDefinition;
        }
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
