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
use Iterator;
use Psr\Log\LoggerInterface;

class DbalGateway implements ReadDbGatewayInterface
{
    private const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var Connection */
    private $connection;

    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $parameterInc;

    /**
     * @param Connection $connection
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * @param string $from
     * @param array $joins
     * @param iterable|Filter[] $filters
     * @param iterable|Sorting[] $sortings
     * @param null|Pagination $pagination
     * @return Iterator|array[]
     * @throws Exception
     */
    public function fetch(
        string      $from,
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
            $this->applyFilter($query, $filter);
        }

        foreach ($sortings as $sorting) {
            $this->applySorting($query, $sorting);
        }

        if ($pagination !== null) {
            $this->applyPagination($query, $pagination);
        }

        $this->logger->debug('Executing query', [
            'sql' => $query->getSQL()
        ]);

        foreach ($query->execute()->iterateAssociative() as $row) {
            yield $row;
        }
    }

    /**
     * @param QueryBuilder $query
     * @param Filter $filter
     * @throws InvalidInputException
     */
    private function applyFilter(QueryBuilder $query, Filter $filter): void
    {
        switch (true) {
            case $filter instanceof RangeFilter:
                $this->applyRangeFilter($query, $filter);
                break;
            case $filter instanceof EqualityFilter:
                $this->applyEqualityFilter($query, $filter);
                break;
            case $filter instanceof PatternFilter:
                $this->applyPatternFilter($query, $filter);
                break;
            default:
                throw new InvalidInputException('Unsupported filter type');
        }
    }

    /**
     * @param QueryBuilder $query
     * @param RangeFilter $filter
     * @throws InvalidInputException
     */
    private function applyRangeFilter(QueryBuilder $query, RangeFilter $filter): void
    {
        if ($filter->getMinValue() === null && $filter->getMaxValue() === null) {
            $this->logger->warning('Ignoring empty RangeFilter', [
                'field' => $filter->getField()
            ]);

            return;
        }

        if ($filter->getMinValue() === $filter->getMaxValue()) {
            $operator = $filter->getMode() === Filter::MODE_INCLUDE ? '=' : '<>';
            $paramId = $this->bindParameter($query, $filter->getMinValue());
            $query->andWhere(sprintf('%s %s :%s', $filter->getField(), $operator, $paramId));

            return;
        }

        if ($filter->getMinValue() !== null) {
            $minParamId = $this->bindParameter($query, $filter->getMinValue());
            $query->andWhere(sprintf('%s >= :%s', $filter->getField(), $minParamId));
        }

        if ($filter->getMaxValue() !== null) {
            $maxParamId = $this->bindParameter($query, $filter->getMaxValue());
            $query->andWhere(sprintf('%s <= :%s', $filter->getField(), $maxParamId));
        }
    }

    /**
     * @param QueryBuilder $query
     * @param EqualityFilter $filter
     * @throws InvalidInputException
     */
    private function applyEqualityFilter(QueryBuilder $query, EqualityFilter $filter): void
    {
        if (count($filter->getValues()) === 0) {
            $this->logger->warning('Ignoring empty EqualityFilter', [
                'field' => $filter->getField()
            ]);

            return;
        }

        // Use equals-operator if only one value
        if (count($filter->getValues()) === 1) {
            $operator = $filter->getMode() === Filter::MODE_INCLUDE ? '=' : '<>';
            $paramId = $this->bindParameter($query, current($filter->getValues()));
            $query->andWhere(sprintf('%s %s :%s', $filter->getField(), $operator, $paramId));

            return;
        }

        // Use IN-Operator if there are multiple values
        $operator = $filter->getMode() === Filter::MODE_INCLUDE ? 'IN' : 'NOT IN';
        $paramId = $this->bindParameter($query, $filter->getValues());
        $query->andWhere(sprintf('%s %s (:%s)', $filter->getField(), $operator, $paramId));
    }

    /**
     * @param QueryBuilder $query
     * @param PatternFilter $filter
     * @throws InvalidInputException
     */
    private function applyPatternFilter(QueryBuilder $query, PatternFilter $filter): void
    {
        $operator = $filter->getMode() === Filter::MODE_INCLUDE ? 'LIKE' : 'NOT LIKE';
        $pattern = $filter->getPattern();

        // Escape percent and underscore
        $pattern = str_replace(['%', '_'], ['\%', '\_'], $pattern);

        // Translate wildcard characters
        $pattern = str_replace(['*', '?'], ['%', '_'], $pattern);

        $paramId = $this->bindParameter($query, $pattern);
        $query->andWhere(sprintf('%s %s :%s', $filter->getField(), $operator, $paramId));
    }

    /**
     * @param QueryBuilder $query
     * @param Sorting $sorting
     */
    private function applySorting(QueryBuilder $query, Sorting $sorting): void
    {
        $query->addOrderBy($sorting->getField(), $sorting->getDirection());
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
     * @param mixed $value
     * @return string
     * @throws InvalidInputException
     */
    private function bindParameter(QueryBuilder $query, $value): string
    {
        switch (strtolower(gettype($value))) {
            case 'string':
                $type = ParameterType::STRING;
                break;
            case 'integer':
                $type = ParameterType::INTEGER;
                break;
            case 'boolean':
                $type = ParameterType::BOOLEAN;
                break;
            case 'object':
                if ($value instanceof DateTimeInterface) {
                    $value = $value->format(self::MYSQL_DATE_FORMAT);
                    $type = ParameterType::STRING;
                } else {
                    throw new InvalidInputException('Unsupported object type for query parameter');
                }
                break;
            case 'array':
                if (is_int(current($value))) {
                    $type = Connection::PARAM_INT_ARRAY;
                } else {
                    $type = Connection::PARAM_STR_ARRAY;
                }
                break;
            case 'null';
                $type = ParameterType::NULL;
                break;
            default:
                throw new InvalidInputException('Unsupported type for query parameter');
        }

        $paramId = 'param_' . ++$this->parameterInc;

        $query->setParameter($paramId, $value, $type);

        return $paramId;
    }
}

