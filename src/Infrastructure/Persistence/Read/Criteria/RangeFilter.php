<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use DateTimeInterface;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\FloatField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;

class RangeFilter extends Filter
{
    private null|int|float|DateTimeInterface $minValue;

    private null|int|float|DateTimeInterface $maxValue;

    public function __construct(IntegerField|FloatField|DateTimeField $field, string $mode, null|int|float|DateTimeInterface $minValue, null|int|float|DateTimeInterface $maxValue)
    {
        $minValue !== null || $maxValue !== null || throw new InvalidInputException('filterMissingValues', [$field->getName()]);

        $this->field = $field;
        $this->mode = $mode;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;

        if ($this->minValue !== null) {
            $this->field->validate($this->minValue);
        }

        if ($this->maxValue !== null) {
            $this->field->validate($this->maxValue);
        }
    }

    public function getMinValue(): null|int|float|DateTimeInterface
    {
        return $this->minValue;
    }

    public function getMaxValue(): null|int|float|DateTimeInterface
    {
        return $this->maxValue;
    }
}
