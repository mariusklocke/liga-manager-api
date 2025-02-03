<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class PatternFilter extends Filter
{
    private string $pattern;

    public function __construct(StringField $field, string $mode, string $pattern)
    {
        $this->field = $field;
        $this->mode = $mode;
        $this->pattern = $pattern;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
