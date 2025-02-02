<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\EmbeddedObjectField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use RuntimeException;

abstract class AbstractRepository
{
    /** @var ReadDbGatewayInterface */
    protected ReadDbGatewayInterface $gateway;

    /** @var Hydrator */
    protected Hydrator $hydrator;

    /** @var array|Field[] */
    protected array $flattenedFieldDefinitions;

    public function __construct(ReadDbGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
        $this->hydrator = new Hydrator($this->getFieldDefinitions());
        $this->flattenedFieldDefinitions = $this->flattenFieldDefinitions($this->getFieldDefinitions());
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
            $this->flattenedFieldDefinitions,
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
            $this->flattenedFieldDefinitions,
            [],
            [new EqualityFilter($this->getField('id'), Filter::MODE_INCLUDE, [$id])]
        ));
    }

    protected function flattenFieldDefinitions(array $fieldDefinitions): array
    {
        $flattened = [];

        foreach ($fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition instanceof EmbeddedObjectField) {
                foreach ($fieldDefinition->getSubFields() as $subField) {
                    $subField = $subField->withName($fieldDefinition->getName() . '_' . $subField->getName());
                    $flattened[$subField->getName()] = $subField;
                }
                continue;
            }

            $flattened[$fieldDefinition->getName()] = $fieldDefinition;
        }

        return $flattened;
    }

    /**
     * @return array|Field[]
     */
    abstract protected function getFieldDefinitions(): array;

    /**
     * @return string
     */
    abstract protected function getTableName(): string;

    public function getField(string $name): Field
    {
        foreach ($this->getFieldDefinitions() as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        throw new RuntimeException(sprintf('Unknown field "%s" for table "%s"', $name, $this->getTableName()));
    }
}
