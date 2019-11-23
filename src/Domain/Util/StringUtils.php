<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

class StringUtils
{
    /**
     * Transforms a name from CamelCase to separated lowercase
     *
     * @param string $subject A name like "MatchLocated"
     * @param string $separator A separator character to use
     * @return string The transformed name like "match:located"
     */
    public static function camelCaseToSeparatedLowercase(string $subject, string $separator = ':')
    {
        $words = preg_split('/(?=[A-Z])/', lcfirst($subject));
        $words = array_map(function (string $word) {
            return strtolower($word);
        }, $words);
        return implode($separator, $words);
    }

    /**
     * Strips the namespace part of a fully qualified class name
     *
     * @param string $className
     * @return string
     */
    public static function stripNamespace(string $className): string
    {
        $lastSlashPosition = strrpos($className, '\\');

        if (false === $lastSlashPosition) {
            return $className;
        }

        return substr($className, $lastSlashPosition + 1);
    }
}