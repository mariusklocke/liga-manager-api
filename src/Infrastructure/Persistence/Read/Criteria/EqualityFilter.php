<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use DateTimeInterface;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class EqualityFilter extends Filter
{
    /** @var array|int[]|string[]|DateTimeInterface[] */
    private array $values;

    public function __construct(string $field, string $mode, array $values)
    {
        if (count($values) === 0) {
            throw new InvalidInputException('Invalid EqualityFilter: Array of values must not be empty');
        }

        $this->field = $field;
        $this->mode = $mode;
        $this->values = $values;
    }

    /**
     * @return array|int[]|string[]|DateTimeInterface[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function validate(Field $fieldDefinition): void
    {
        $inputName = 'EqualityFilter.' . $fieldDefinition->getName();

        // Validate Field type
        switch (get_class($fieldDefinition)) {
            case IntegerField::class:
                $validator = function ($value) use ($inputName): void {
                    TypeAssert::assertInteger($value, $inputName);
                };
                break;
            case StringField::class:
                $validator = function ($value) use ($inputName): void {
                    TypeAssert::assertString($value, $inputName);
                };
                break;
            case DateTimeField::class:
                $validator = function ($value) use ($inputName): void {
                    TypeAssert::assertInstanceOf($value, DateTimeInterface::class, $inputName);
                };
                break;
            default:
                throw new InvalidInputException('Invalid EqualityFilter: Unsupported field type');
        }

        foreach ($this->values as $value) {
            $validator($value);
        }
    }
}
