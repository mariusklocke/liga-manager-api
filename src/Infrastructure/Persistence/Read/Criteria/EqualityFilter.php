<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use DateTimeInterface;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class EqualityFilter extends Filter
{
    /** @var array|int[]|string[]|DateTimeInterface[] */
    private array $values;

    public function __construct(IntegerField|StringField|DateTimeField $field, string $mode, array $values)
    {
        count($values) > 0 || throw new InvalidInputException('filterMissingValues', [$field->getName()]);

        $this->field = $field;
        $this->mode = $mode;
        $this->values = $values;
        
        foreach ($this->values as $value) {
            $this->field->validate($value);
        }
    }

    /**
     * @return array|int[]|string[]|DateTimeInterface[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
