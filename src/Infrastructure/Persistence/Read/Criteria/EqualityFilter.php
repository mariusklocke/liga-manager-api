<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use DateTimeInterface;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class EqualityFilter extends Filter
{
    /** @var array|int[]|string[]|DateTimeInterface[] */
    private $values;

    public function __construct(string $field, string $mode, array $values)
    {
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

    public function validate(?Field $fieldDefinition): void
    {
        parent::validate($fieldDefinition);

        switch (get_class($fieldDefinition)) {
            case IntegerField::class:
                $validator = function ($value): void {
                    TypeAssert::assertInteger($value, 'EqualityFilter.' . $this->field);
                };
                break;
            case StringField::class:
                $validator = function ($value): void {
                    TypeAssert::assertString($value, 'EqualityFilter.' . $this->field);
                };
                break;
            case DateTimeField::class:
                $validator = function ($value): void {
                    TypeAssert::assertInstanceOf($value, DateTimeInterface::class, 'EqualityFilter.' . $this->field);
                };
                break;
            default:
                throw new InvalidInputException('Unsupported field type for EqualityFilter');
        }

        if (count($this->values) === 0) {
            throw new InvalidInputException('Invalid EqualityFilter: Array of values must not be empty');
        }

        foreach ($this->values as $value) {
            $validator($value);
        }
    }
}
