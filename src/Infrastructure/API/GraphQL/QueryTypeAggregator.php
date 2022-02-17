<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

class QueryTypeAggregator
{
    /** @var QueryTypeInterface[] */
    private array $types;

    /**
     * @param QueryTypeInterface[] $types
     */
    public function __construct(array $types)
    {
        $this->types = array_map(function (QueryTypeInterface $type) {
            return $type;
        }, $types);
    }

    /**
     * @return array
     */
    public function aggregate(): array
    {
        $result = [];
        foreach ($this->types as $type) {
            $result += $type->getQueries();
        }

        return $result;
    }
}
