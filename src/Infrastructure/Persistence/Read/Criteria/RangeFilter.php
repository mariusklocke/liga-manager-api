<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Domain\Util\Assert;

class RangeFilter extends Filter
{
    /** @var mixed */
    private $minValue;

    /** @var mixed */
    private $maxValue;

    public function __construct(string $field, string $mode, $minValue, $maxValue)
    {
        Assert::false(
            $minValue === null && $maxValue === null,
            'Cannot use RangeFilter with null for minValue and maxValue'
        );

        $this->field = $field;
        $this->mode = $mode;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    /**
     * @return mixed
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * @return mixed
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }
}
