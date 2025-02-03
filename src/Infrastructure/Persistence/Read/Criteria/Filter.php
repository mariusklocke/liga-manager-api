<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

abstract class Filter
{
    public const MODE_INCLUDE = 'include';
    public const MODE_EXCLUDE = 'exclude';

    protected Field $field;

    protected string $mode;

    public function getField(): Field
    {
        return $this->field;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
