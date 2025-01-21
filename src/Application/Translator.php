<?php declare(strict_types=1);

namespace HexagonalPlayground\Application;

use DateTimeInterface;
use IntlDateFormatter;
use RuntimeException;

class Translator
{
    /** @var string[][] */
    private array $translations;
    
    /** @var IntlDateFormatter[] */
    private array $dateFormatters;

    public function __construct()
    {
        $this->translations = [];
        $this->dateFormatters = [];
    }

    public function get(string $locale, string $key, array $params = []): string
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

    public function getLocalizedDateTime(string $locale, DateTimeInterface $dateTime): string
    {
        if (!isset($this->dateFormatters[$locale])) {
            $this->dateFormatters[$locale] = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG);
        }

        return $this->dateFormatters[$locale]->format($dateTime);
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