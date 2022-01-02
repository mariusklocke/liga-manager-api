<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use DateTimeInterface;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\FloatField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;

class RangeFilter extends Filter
{
    /** @var null|int|float|DateTimeInterface */
    private $minValue;

    /** @var null|int|float|DateTimeInterface */
    private $maxValue;

    public function __construct(string $field, string $mode, $minValue, $maxValue)
    {
        $this->field = $field;
        $this->mode = $mode;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    /**
     * @return null|int|float|DateTimeInterface
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * @return null|int|float|DateTimeInterface
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    public function validate(?Field $fieldDefinition): void
    {
        parent::validate($fieldDefinition);

        if ($this->minValue === null && $this->maxValue === null) {
            throw new InvalidInputException('Invalid RangeFilter: Neither minValue nor maxValue given');
        }

        switch (get_class($fieldDefinition)) {
            case IntegerField::class:
                $validator = function ($value): void {
                    TypeAssert::assertInteger($value, 'RangeFilter.' . $this->field);
                };
                break;
            case FloatField::class:
                $validator = function ($value): void {
                    TypeAssert::assertNumber($value, 'RangeFilter.' . $this->field);
                };
                break;
            case DateTimeField::class:
                $validator = function ($value): void {
                    TypeAssert::assertInstanceOf($value, DateTimeInterface::class, 'RangeFilter.' . $this->field);
                };
                break;
            default:
                throw new InvalidInputException('Invalid RangeFilter: Unsupported field type');
        }

        if ($this->minValue !== null) {
            $validator($this->minValue);
        }

        if ($this->maxValue !== null) {
            $validator($this->maxValue);
        }
    }
}
