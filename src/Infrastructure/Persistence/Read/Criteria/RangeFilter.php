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
        if ($minValue === null && $maxValue === null) {
            throw new InvalidInputException('Invalid RangeFilter: Neither minValue nor maxValue given');
        }

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

    public function validate(Field $fieldDefinition): void
    {
        $inputName = 'RangeFilter.' . $fieldDefinition->getName();

        switch (get_class($fieldDefinition)) {
            case IntegerField::class:
                $validator = function ($value) use ($inputName): void {
                    TypeAssert::assertInteger($value, $inputName);
                };
                break;
            case FloatField::class:
                $validator = function ($value) use ($inputName): void {
                    TypeAssert::assertNumber($value, $inputName);
                };
                break;
            case DateTimeField::class:
                $validator = function ($value) use ($inputName): void {
                    TypeAssert::assertInstanceOf($value, DateTimeInterface::class, $inputName);
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
