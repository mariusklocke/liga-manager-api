<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

class PatternFilter extends Filter
{
    /** @var string */
    private $pattern;

    public function __construct(string $field, string $mode, string $pattern)
    {
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
