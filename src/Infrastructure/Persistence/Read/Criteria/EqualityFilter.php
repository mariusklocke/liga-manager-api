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
        Assert::true(count($values) > 0, 'Cannot use EqualityFilter with an empty array of values');
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

    public function validate(array $fieldDefinitions): void
    {
        parent::validate($fieldDefinitions);

        // TODO: Use proper type definitions
        $requiredType = $fieldDefinitions[$this->field];

        foreach ($this->values as $index => $value) {
            $type = strtolower(gettype($value));

            if (class_exists($requiredType)) {
                Assert::true($value instanceof $requiredType, sprintf(
                    'Invalid EqualityFilter value at index %d. Got type %s, but expected instance of %s',
                    $index,
                    is_object($value) ? get_class($value) : $type,
                    $requiredType
                ));

                continue;
            }

            Assert::true($type === $requiredType, sprintf(
                'Invalid EqualityFilter value at index %d. Got type %s, but expected %s.',
                $index,
                $type,
                $requiredType
            ));
        }
    }
}
