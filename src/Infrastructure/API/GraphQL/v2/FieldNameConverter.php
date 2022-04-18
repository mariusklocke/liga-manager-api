<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

class FieldNameConverter
{
    public function convert(iterable $input): array
    {
        $result = [];

        foreach ($input as $key => $value) {
            if (is_string($key)) {
                $key = $this->convertKey($key);
            }
            $result[$key] = is_array($value) ? $this->convert($value) : $value;
        }

        return $result;
    }

    private function convertKey(string $key): string
    {
        $words = explode('_', $key);
        $converted = array_shift($words);
        $converted .= implode('', array_map('ucfirst', $words));

        return $converted;
    }
}
