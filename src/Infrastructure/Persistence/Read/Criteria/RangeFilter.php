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

    public function validate(array $fieldDefinitions): void
    {
        parent::validate($fieldDefinitions);

        // TODO: Use proper type definitions
        $requiredType = $fieldDefinitions[$this->field];

        $minValueType = strtolower(gettype($this->minValue));
        $maxValueType = strtolower(gettype($this->maxValue));

        if (class_exists($requiredType)) {
            Assert::true($this->minValue === null || $this->minValue instanceof $requiredType, sprintf(
                'Invalid RangeFilter minValue. Got type %s, but expected instance of %s',
                is_object($this->minValue) ? get_class($this->minValue) : $minValueType,
                $requiredType
            ));

            Assert::true($this->maxValue === null || $this->maxValue instanceof $requiredType, sprintf(
                'Invalid RangeFilter maxValue. Got type %s, but expected instance of %s',
                is_object($this->maxValue) ? get_class($this->maxValue) : $maxValueType,
                $requiredType
            ));

            return;
        }

        Assert::true($this->minValue === null || $minValueType === $requiredType, sprintf(
            'Invalid RangeFilter minValue. Got type %s, but expected %s.',
            $minValueType,
            $requiredType
        ));

        Assert::true($this->maxValue === null || $maxValueType === $requiredType, sprintf(
            'Invalid RangeFilter maxValue. Got type %s, but expected %s.',
            $maxValueType,
            $requiredType
        ));
    }
}
