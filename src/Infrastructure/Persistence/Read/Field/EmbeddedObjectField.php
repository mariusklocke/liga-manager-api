<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

class EmbeddedObjectField extends Field
{
    /** @var array|Field[] */
    private $subFields;

    public function __construct(string $name, bool $isNullable, iterable $subFields)
    {
        parent::__construct($name, $isNullable);

        foreach ($subFields as $subField) {
            $this->subFields[] = $subField;
        }
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
}
