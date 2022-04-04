<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

abstract class Filter
{
    public const MODE_INCLUDE = 'include';
    public const MODE_EXCLUDE = 'exclude';

    /** @var string */
    protected string $field;

    /** @var string */
    protected string $mode;

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
    public function getMode(): string
    {
        return $this->mode;
    }

    abstract public function validate(Field $fieldDefinition): void;
}
