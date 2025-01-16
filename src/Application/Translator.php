<?php declare(strict_types=1);

namespace HexagonalPlayground\Application;

use RuntimeException;

class Translator
{
    private array $translations;

    public function __construct()
    {
        $this->translations = [];
    }

    public function get(string $key, array $params = [], string $locale = 'de'): string
    {
        if (!isset($this->translations[$locale])) {
            $filePath = join(
                DIRECTORY_SEPARATOR,
                [__DIR__, '..', '..', 'locales', "$locale.json"]
            );

            if (!file_exists($filePath)) {
                throw new RuntimeException("Failed to find translations for locale $locale");
            }

            $this->translations[$locale] = $this->flattenArray(json_decode(file_get_contents($filePath), true));
        }

        $value = $this->translations[$locale][$key] ?? '';

        if ($value !== '' && count($params) > 0) {
            $value = sprintf($value, ...$params);
        }

        return $value;
    }

    private function flattenArray(array $array, string $parentKey = ''): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            $newKey = $parentKey ? $parentKey . '.' . $key : $key;

            if (is_array($value)) {
                $flattened = array_merge($flattened, $this->flattenArray($value, $newKey));
            } else {
                $flattened[$newKey] = $value;
            }
        }

        return $flattened;
    }
}