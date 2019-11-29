<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\Exception\InvalidInputException;
use Psr\Http\Message\StreamInterface;

class L98IniParser
{
    /** @var array */
    private $sections;

    public function __construct(StreamInterface $stream)
    {
        $this->parse($stream);
    }

    private function parse(StreamInterface $stream): void
    {
        $stream->rewind();
        $rawData = $stream->getContents();

        // Add quotes around all values
        $iniData = preg_replace('/^([A-Za-z0-9]+)=(.*)$/m', '${1}="${2}"', $rawData);
        $parsedData = parse_ini_string(
            $iniData,
            true
        );
        if (!is_array($parsedData)) {
            throw new InvalidInputException('Failed parsing L98 ini data');
        }

        $this->sections = $parsedData;
    }

    public function getSection(string $key): ?array
    {
        return isset($this->sections[$key]) ? $this->sections[$key] : null;
    }

    public function getSectionValue(string $sectionKey, string $valueKey): ?string
    {
        $section = $this->getSection($sectionKey);
        return ($section !== null && isset($section[$valueKey])) ? $section[$valueKey] : null;
    }
}
