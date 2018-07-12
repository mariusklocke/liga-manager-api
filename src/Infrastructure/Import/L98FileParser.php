<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use Generator;
use HexagonalPlayground\Application\InputParser;

class L98FileParser
{
    /** @var array */
    private $data;

    public function __construct(string $path)
    {
        $data = parse_ini_file($path, true);
        if (!is_array($data)) {
            throw new \Exception('Cannot parse L98 file');
        }
        $this->data = $data;
    }

    private function getSection(string $key): ?array
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    private function getValue(string $sectionKey, string $valueKey): ?string
    {
        $section = $this->getSection($sectionKey);
        return ($section !== null && isset($section[$valueKey])) ? $section[$valueKey] : null;
    }

    /**
     * @return Generator|L98MatchModel[]
     */
    public function getMatches(): Generator
    {
        $matchDay = 1;
        while ($round = $this->getSection(sprintf('Round%d', $matchDay))) {
            $matchIndex = 1;
            while (isset($round['TA' . $matchIndex])) {
                yield new L98MatchModel(
                    InputParser::parseInteger($round['TA' . $matchIndex]),
                    InputParser::parseInteger($round['TB' . $matchIndex]),
                    InputParser::parseInteger($round['GA' . $matchIndex]),
                    InputParser::parseInteger($round['GB' . $matchIndex]),
                    $round['AT' . $matchIndex] !== '' ? InputParser::parseInteger($round['AT' . $matchIndex]) : null,
                    $matchDay
                );

                $matchIndex++;
            }
            $matchDay++;
        }
    }

    /**
     * @return Generator
     */
    public function getTeams(): Generator
    {
        $i = 1;
        while ($name = $this->getValue('Teams', (string)$i)) {
            if ($name !== 'Freilos') {
                yield new L98TeamModel($i, $name);
            }
            $i++;
        }
    }

    public function getSeason(): L98SeasonModel
    {
        $name = $this->getValue('Options', 'Name');
        return new L98SeasonModel($name);
    }
}