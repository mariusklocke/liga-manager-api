<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

class StringUtils
{
    /**
     * Transforms a name from CamelCase to separated lowercase
     *
     * @param string $subject A name like "MatchLocated"
     * @param string $separator A separator character to use
     * @return string The transformed name like "match:located"
     */
    public static function camelCaseToSeparatedLowercase(string $subject, string $separator = ':'): string
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
        $pos = strrpos($className, '\\');

        return false !== $pos ? substr($className, $pos + 1) : $className;
    }

    /**
     * Calculates the string length (multibyte character is counted as 1)
     *
     * @param string $value
     * @return int
     */
    public static function length(string $value): int
    {
        return mb_strlen($value);
    }

    /**
     * Determines if a string is valid email address
     *
     * @param string $value
     * @return bool
     */
    public static function isValidEmailAddress(string $value): bool
    {
        return (new EmailValidator())->isValid($value, new RFCValidation());
    }

    /**
     * Determines if a string is a valid URL
     * 
     * @param string $value
     * @return bool
     */
    public static function isValidUrl(string $value): bool
    {
        $parsed = parse_url($value);

        return is_array($parsed) && isset($parsed['scheme']) && isset($parsed['host']);
    }
}
