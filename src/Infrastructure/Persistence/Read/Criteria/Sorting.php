<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

class Sorting
{
    public const DIRECTION_ASCENDING = 'ASC';
    public const DIRECTION_DESCENDING = 'DESC';

    /** @var Field */
    private Field $field;

    /** @var string */
    private string $direction;

    public function __construct(?Field $field, string $direction)
    {
        if ($field === null) {
            throw new InvalidInputException('Invalid Sorting: Unknown field');
        }

        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * @return Field
     */
    public function getField(): Field
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
