<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class PatternFilter extends Filter
{
    /** @var string */
    private string $pattern;

    public function __construct(?Field $field, string $mode, string $pattern)
    {
        if ($field === null) {
            throw new InvalidInputException('Invalid PatternFilter: Unknown field');
        }

        if (!($field instanceof StringField)) {
            throw new InvalidInputException('Invalid PatternFilter: Can only be used on string fields');
        }

        $this->field = $field;
        $this->mode = $mode;
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }
}
