<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

class RangeFilter extends Filter
{
    /** @var mixed */
    private $minValue;

    /** @var mixed */
    private $maxValue;

    public function __construct(string $field, string $mode, $minValue, $maxValue)
    {
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
