<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

class Limits
{
    public readonly int $uploadFileSize;
    public readonly array $uploadFileTypes;
    public readonly int $requestsPerSecond;

    public function __construct()
    {
        $this->uploadFileSize = $this->parseByteSize(ini_get('upload_max_filesize'));
        $this->uploadFileTypes = ['image/webp'];
        $this->requestsPerSecond = 5;
    }

    /**
     * Converts a byte size string with SI-prefixes to number of bytes
     *
     * @param string $byteSize
     * @return int
     */
    private function parseByteSize(string $byteSize): int
    {
        $factorMap = [
            'K' => pow(2, 10),
            'M' => pow(2, 20),
            'G' => pow(2, 30)
        ];

        $prefix = $byteSize[strlen($byteSize) - 1];
        if (!array_key_exists($prefix, $factorMap)) {
            return (int)$byteSize;
        }

        return substr($byteSize, 0, -1) * $factorMap[$prefix];
    }
}