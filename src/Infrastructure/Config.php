<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class Config
{
    private array $filePaths;
    private array $values;

    public function __construct(array $filePaths)
    {
        $this->filePaths = $filePaths;
        $this->load();
    }

    public function getValue(string $key, $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    private function load(): void
    {
        if (file_exists($this->filePaths['json'])) {
            $values = json_decode(file_get_contents($this->filePaths['json']), true);
        } else {
            $values = getenv();
        }
        $this->values = [];
        foreach ($values as $key => $value) {
            $this->values[$this->normalizeKey($key)] = $value;
        }
    }

    private function normalizeKey(string $key): string
    {
        $words = explode('_', $key);
        $words = array_map('strtolower', $words);

        return implode('.', $words);
    }
}
