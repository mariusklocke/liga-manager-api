<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

class Sorting
{
    public const DIRECTION_ASCENDING = 'ASC';
    public const DIRECTION_DESCENDING = 'DESC';

    private Field $field;

    private string $direction;

    public function __construct(Field $field, string $direction)
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
