<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeImmutable;
use DateTimeZone;
use LogicException;

class Hydrator
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_DATETIME = DateTimeImmutable::class;
    public const TYPE_SERIALIZED_ARRAY = 'serializedArray';

    /** @var array */
    private $fields;

    /** @var DateTimeZone */
    private $dateTimeZone;

    /**
     * @param iterable $fields
     */
    public function __construct(iterable $fields)
    {
        $this->fields = [];

        foreach ($fields as $name => $type) {
            $this->fields[$name] = $type;
        }

        $this->dateTimeZone = new DateTimeZone('UTC');
    }

    /**
     * @param iterable|array[] $rows
     * @return array|null
     */
    public function hydrateOne(iterable $rows): ?array
    {
        foreach ($rows as $row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * @param iterable|array[] $rows
     * @param string|null $groupBy
     * @return array
     */
    public function hydrateMany(iterable $rows, ?string $groupBy = null): array
    {
        $result = [];

        foreach ($rows as $row) {
            $row = $this->hydrate($row);

            if ($groupBy !== null) {
                $result[$row[$groupBy]][] = $row;
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Converts a database row to API-compatible format
     *
     * @param array $row
     * @return array
     */
    private function hydrate(array $row): array
    {
        $result = [];

        foreach ($this->fields as $fieldName => $fieldType) {
            if (is_array($fieldType)) {
                $embedded = [];

                foreach ($row as $innerField => $innerValue) {
                    if ($innerValue === null) {
                        continue;
                    }

                    if (str_starts_with($innerField, $fieldName)) {
                        $subField = str_replace($fieldName . '_', '', $innerField);
                        $embedded[$subField] = $innerValue;
                    }
                }

                $result[$fieldName] = count($embedded) ? (new static($fieldType))->hydrate($embedded) : null;
                break;
            }

            switch ($fieldType) {
                case self::TYPE_STRING:
                    $result[$fieldName] = $this->string($row[$fieldName]);
                    break;
                case self::TYPE_DATETIME:
                    $result[$fieldName] = $this->dateTime($row[$fieldName]);
                    break;
                case self::TYPE_INT:
                    $result[$fieldName] = $this->int($row[$fieldName]);
                    break;
                case self::TYPE_FLOAT:
                    $result[$fieldName] = $this->float($row[$fieldName]);
                    break;
                case self::TYPE_SERIALIZED_ARRAY:
                    $result[$fieldName] = $this->unserializeArray($row[$fieldName]);
                    break;
                default:
                    throw new LogicException('Unsupported field type: ' . $fieldType);
            }
        }

        return $result;
    }

    private function int($value): ?int
    {
        return $value !== null ? (int)$value : null;
    }

    private function string($value): ?string
    {
        return $value !== null ? (string)$value : null;
    }

    private function float($value): ?float
    {
        return $value !== null ? (float)$value : null;
    }

    private function dateTime($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = (new DateTimeImmutable($value))
            ->setTimezone($this->dateTimeZone)
            ->format(DATE_ATOM);

        // Adjust timezone identifier for not breaking tests
        return str_replace('+00:00', 'Z', $string);
    }

    private function unserializeArray($value): array
    {
        return unserialize($value);
    }
}
