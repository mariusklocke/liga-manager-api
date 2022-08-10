<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\PatternFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\RangeFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;
use Iterator;

class DbalGateway implements ReadDbGatewayInterface
{
    /** @var Connection */
    private Connection $connection;

    /** @var int */
    private int $parameterInc;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $from
     * @param array $fields
     * @param array $joins
     * @param iterable|Filter[] $filters
     * @param iterable|Sorting[] $sortings
     * @param null|Pagination $pagination
     * @return Iterator|array[]
     * @throws Exception
     */
    public function fetch(
        string      $from,
        array       $fields,
        array       $joins = [],
        iterable    $filters = [],
        iterable    $sortings = [],
        ?Pagination $pagination = null
    ): Iterator {
        $query = $this->connection->createQueryBuilder();
        $query->select('*');
        $query->from($from, count($joins) ? 'f1' : null);

        $j = 0;
        foreach ($joins as $table => $condition) {
            $alias = 'j' . $j;
            $query->join('f1', $table, $alias, $condition);
            $j++;
        }

        $this->parameterInc = 0;

        foreach ($filters as $filter) {
            /** @var Field|null $field */
            $field = $fields[$filter->getField()] ?? null;

            if ($field === null) {
                throw new InvalidInputException('Invalid Filter: Unknown field');
            }

            $filter->validate($field);
            $this->applyFilter($query, $filter, $field);
        }

        foreach ($sortings as $sorting) {
            $field = $fields[$sorting->getField()] ?? null;

            if ($field === null) {
                throw new InvalidInputException('Invalid Sorting: Unknown field');
            }

            $this->applySorting($query, $sorting, $field);
        }

        if ($pagination !== null) {
            $this->applyPagination($query, $pagination);
        }

        foreach ($query->executeQuery()->iterateAssociative() as $row) {
            yield $row;
        }
    }

    /**
     * @param QueryBuilder $query
     * @param Filter $filter
     * @param Field $field
     * @throws InvalidInputException
     */
    private function applyFilter(QueryBuilder $query, Filter $filter, Field $field): void
    {
        switch (true) {
            case $filter instanceof RangeFilter:
                $this->applyRangeFilter($query, $filter, $field);
                break;
            case $filter instanceof EqualityFilter:
                $this->applyEqualityFilter($query, $filter, $field);
                break;
            case $filter instanceof PatternFilter:
                $this->applyPatternFilter($query, $filter, $field);
                break;
            default:
                throw new InvalidInputException('Unsupported filter type');
        }
    }

    /**
     * @param QueryBuilder $query
     * @param RangeFilter $filter
     * @param Field $field
     * @throws InvalidInputException
     */
    private function applyRangeFilter(QueryBuilder $query, RangeFilter $filter, Field $field): void
    {
        if ($filter->getMinValue() === $filter->getMaxValue()) {
            $operator = $filter->getMode() === Filter::MODE_INCLUDE ? '=' : '<>';
            $paramId = $this->bindParameter($query, $field, $filter->getMinValue());
            $query->andWhere(sprintf('%s %s :%s', $field->getName(), $operator, $paramId));

            return;
        }

        $conditions = [];

        if ($filter->getMinValue() !== null) {
            $minParamId = $this->bindParameter($query, $field, $filter->getMinValue());
            $operator = $filter->getMode() === Filter::MODE_INCLUDE ? '>=' : '<';
            $conditions[] = sprintf('%s %s :%s', $field->getName(), $operator, $minParamId);
        }

        if ($filter->getMaxValue() !== null) {
            $maxParamId = $this->bindParameter($query, $field, $filter->getMaxValue());
            $operator = $filter->getMode() === Filter::MODE_INCLUDE ? '<=' : '>';
            $conditions[] = sprintf('%s %s :%s', $field->getName(), $operator, $maxParamId);
        }

        $query->andWhere(implode($filter->getMode() === Filter::MODE_INCLUDE ? ' AND ' : ' OR ', $conditions));
    }

    /**
     * @param QueryBuilder $query
     * @param EqualityFilter $filter
     * @param Field $field
     * @throws InvalidInputException
     */
    private function applyEqualityFilter(QueryBuilder $query, EqualityFilter $filter, Field $field): void
    {
        // Use equals-operator if only one value
        if (count($filter->getValues()) === 1) {
            $operator = $filter->getMode() === Filter::MODE_INCLUDE ? '=' : '<>';
            $paramId = $this->bindParameter($query, $field, current($filter->getValues()));
            $query->andWhere(sprintf('%s %s :%s', $field->getName(), $operator, $paramId));

            return;
        }

        // Use IN-Operator if there are multiple values
        $operator = $filter->getMode() === Filter::MODE_INCLUDE ? 'IN' : 'NOT IN';
        $paramId = $this->bindParameter($query, $field, $filter->getValues());
        $query->andWhere(sprintf('%s %s (:%s)', $field->getName(), $operator, $paramId));
    }

    /**
     * @param QueryBuilder $query
     * @param PatternFilter $filter
     * @param Field $field
     * @throws InvalidInputException
     */
    private function applyPatternFilter(QueryBuilder $query, PatternFilter $filter, Field $field): void
    {
        $operator = $filter->getMode() === Filter::MODE_INCLUDE ? 'LIKE' : 'NOT LIKE';
        $pattern = $filter->getPattern();

        // Escape percent and underscore
        $pattern = str_replace(['%', '_'], ['\%', '\_'], $pattern);

        // Translate wildcard characters
        $pattern = str_replace(['*', '?'], ['%', '_'], $pattern);

        $paramId = $this->bindParameter($query, $field, $pattern);
        $query->andWhere(sprintf('%s %s :%s', $field->getName(), $operator, $paramId));
    }

    /**
     * @param QueryBuilder $query
     * @param Sorting $sorting
     * @param Field $field
     */
    private function applySorting(QueryBuilder $query, Sorting $sorting, Field $field): void
    {
        $query->addOrderBy($field->getName(), $sorting->getDirection());
    }

    /**
     * @param QueryBuilder $query
     * @param Pagination $pagination
     */
    private function applyPagination(QueryBuilder $query, Pagination $pagination): void
    {
        $query->setFirstResult($pagination->getOffset());
        $query->setMaxResults($pagination->getLimit());
    }

    /**
     * @param QueryBuilder $query
     * @param Field $field
     * @param mixed $value
     * @return string
     * @throws InvalidInputException
     */
    private function bindParameter(QueryBuilder $query, Field $field, $value): string
    {
        switch (get_class($field)) {
            case StringField::class:
                $type = is_array($value) ? Connection::PARAM_STR_ARRAY : ParameterType::STRING;
                break;
            case IntegerField::class:
                $type = is_array($value) ? Connection::PARAM_INT_ARRAY : ParameterType::INTEGER;
                break;
            case DateTimeField::class:
                if (!$value instanceof DateTimeInterface) {
                    throw new InvalidInputException('Unsupported filter value for DateTimeField');
                }
                $value = $value->format($this->connection->getDatabasePlatform()->getDateTimeFormatString());
                $type = ParameterType::STRING;
                break;
            case DateField::class:
                if (!$value instanceof DateTimeInterface) {
                    throw new InvalidInputException('Unsupported filter value for DateField');
                }
                $value = $value->format($this->connection->getDatabasePlatform()->getDateFormatString());
                $type = ParameterType::STRING;
                break;
            default:
                throw new InvalidInputException('Unsupported field type for query parameter');
        }

        $paramId = 'param_' . ++$this->parameterInc;

        $query->setParameter($paramId, $value, $type);

        return $paramId;
    }
}

