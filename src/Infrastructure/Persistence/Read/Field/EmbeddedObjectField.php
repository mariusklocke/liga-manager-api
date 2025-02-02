<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

class EmbeddedObjectField extends Field
{
    /** @var array|Field[] */
    private array $subFields;

    public function __construct(string $name, bool $isNullable, iterable $subFields)
    {
        parent::__construct($name, $isNullable);

        $this->subFields = [];
        foreach ($subFields as $subField) {
            $this->subFields[] = $subField;
        }
    }

    public function getSubFields(): array
    {
        return $this->subFields;
    }

    public function hydrate(array $row): ?array
    {
        $result = [];

        foreach ($this->subFields as $subField) {
            $subValue = $subField->hydrate([
                $subField->getName() => $row[$this->getName() . '_' . $subField->getName()] ?? null
            ]);

            if ($subValue !== null) {
                $result[$subField->getName()] = $subValue;
            }
        }

        return count($result) ? $result : null;
    }

    public function validate(mixed $value): void
    {
        // TODO: Implement
    }
}
