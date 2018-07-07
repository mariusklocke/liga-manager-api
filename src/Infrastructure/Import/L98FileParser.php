<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use Generator;
use HexagonalPlayground\Infrastructure\InputParser;

class L98FileParser
{
    /** @var array */
    private $data;

    /** @var InputParser */
    private $inputParser;

    public function __construct(string $path)
    {
        $data = parse_ini_file($path, true);
        if (!is_array($data)) {
            throw new \Exception('Cannot parse L98 file');
        }
        $this->data = $data;
        $this->inputParser = new InputParser();
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
                    $this->inputParser->parseInteger($round['TA' . $matchIndex]),
                    $this->inputParser->parseInteger($round['TB' . $matchIndex]),
                    $this->inputParser->parseInteger($round['GA' . $matchIndex]),
                    $this->inputParser->parseInteger($round['GB' . $matchIndex]),
                    $this->inputParser->parseInteger($round['AT' . $matchIndex]),
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
        while ($name = $this->getValue('Team', (string)$i)) {
            yield new L98TeamModel($i, $name);
        }
    }

    public function getSeason(): L98SeasonModel
    {
        $name = $this->getValue('Options', 'Name');
        return new L98SeasonModel($name);
    }
}