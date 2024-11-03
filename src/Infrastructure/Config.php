<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class Config
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getValue(string $key, $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public static function load(array $filePaths): self
    {
        if (file_exists($filePaths['json'])) {
            $values = json_decode(file_get_contents($filePaths['json']), true);
        } else {
            $values = getenv();
        }
        $normalized = [];
        foreach ($values as $key => $value) {
            $normalized[self::normalizeKey($key)] = $value;
        }

        return new self($normalized);
    }

    private static function normalizeKey(string $key): string
    {
        $words = explode('_', $key);
        $words = array_map('strtolower', $words);

        return implode('.', $words);
    }
}
