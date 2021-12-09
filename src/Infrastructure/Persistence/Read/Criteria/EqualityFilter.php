<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Domain\Util\Assert;

class EqualityFilter extends Filter
{
    /** @var array */
    private $values;

    public function __construct(string $field, string $mode, array $values)
    {
        Assert::false(count($values) === 0, 'Cannot use EqualityFilter with an empty array of values');
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
