<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

class Sorting
{
    public const DIRECTION_ASCENDING = 'ASC';
    public const DIRECTION_DESCENDING = 'DESC';

    /** @var string */
    private string $field;

    /** @var string */
    private string $direction;

    public function __construct(string $field, string $direction)
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}
