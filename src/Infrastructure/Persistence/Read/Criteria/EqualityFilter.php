<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

class EqualityFilter extends Filter
{
    /** @var array */
    private $values;

    public function __construct(string $field, string $mode, array $values)
    {
        $this->field = $field;
        $this->mode = $mode;
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
