<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\ReadDbAdapterInterface;

class AbstractRepository
{
    /** @var ReadDbAdapterInterface */
    private $db;

    public function __construct(ReadDbAdapterInterface $readDbAdapter)
    {
        $this->db = $readDbAdapter;
    }

    protected function getDb()
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
}
