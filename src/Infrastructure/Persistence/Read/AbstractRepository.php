<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class AbstractRepository
{
    const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var ReadDbAdapterInterface */
    private $db;

    public function __construct(ReadDbAdapterInterface $readDbAdapter)
    {
        $this->db = $readDbAdapter;
    }

    /**
     * @return ReadDbAdapterInterface
     */
    protected function getDb(): ReadDbAdapterInterface
    {
        return $this->db;
    }

    /**
     * @param array $subject
     * @param string $objectProperty
     * @param string $separator
     * @return array
     */
    protected function reconstructEmbeddedObject(array $subject, string $objectProperty, string $separator = '_'): array
    {
        $hasValues  = false;
        $properties = array_filter(array_keys($subject), function($key) use ($objectProperty) {
            return strpos($key, $objectProperty) === 0;
        });
        foreach ($properties as $property) {
            list(,$innerProperty) = explode($separator, $property, 2);
            $subject[$objectProperty][$innerProperty] = $subject[$property];
            $hasValues = $hasValues || ($subject[$property] !== null);
            unset($subject[$property]);
        }
        if (!$hasValues) {
            $subject[$objectProperty] = null;
        }
        return $subject;
    }

    /**
     * @param string $field
     * @param string|null $alias
     * @return string
     */
    protected function getDateFormat(string $field, string $alias = null): string
    {
        if (null === $alias) {
            $alias = $field;
        }
        return "DATE_FORMAT($field, '%Y-%m-%dT%TZ') as $alias";
    }
}
