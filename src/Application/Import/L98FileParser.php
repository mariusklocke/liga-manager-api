<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\InputParser;

class L98FileParser
{
    /** @var array */
    private $data;

    public function __construct(string $path)
    {
        // Add quotes around all values
        $iniData = preg_replace('/^([A-Za-z0-9]+)=(.*)$/m', '${1}="${2}"', file_get_contents($path));
        $data = parse_ini_string(
            $iniData,
            true
        );
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
     * @param array $teams
     * @return L98MatchDayModel[]
     */
    private function getMatchDays(array $teams): array
    {
        $result = [];
        $matchDayIndex = 1;
        while ($round = $this->getSection(sprintf('Round%d', $matchDayIndex))) {
            $matchDay = new L98MatchDayModel(
                $matchDayIndex,
                InputParser::parseDateTime($round['D1']),
                InputParser::parseDateTime($round['D2'])
            );

            $matchIndex = 1;
            while (isset($round['TA' . $matchIndex])) {
                $homeTeam  = $teams[InputParser::parseInteger($round['TA' . $matchIndex])] ?? null;
                $guestTeam = $teams[InputParser::parseInteger($round['TB' . $matchIndex])] ?? null;
                if (null !== $homeTeam && null !== $guestTeam) {
                    $match = new L98MatchModel(
                        $homeTeam,
                        $guestTeam,
                        InputParser::parseInteger($round['GA' . $matchIndex]),
                        InputParser::parseInteger($round['GB' . $matchIndex]),
                        $round['AT' . $matchIndex] !== '' ? InputParser::parseInteger($round['AT' . $matchIndex]) : null,
                        $matchDayIndex
                    );
                    $matchDay->addMatch($match);
                }
                $matchIndex++;
            }
            $result[] = $matchDay;
            $matchDayIndex++;
        }
        return $result;
    }

    /**
     * @return L98TeamModel[]
     */
    private function getTeams(): array
    {
        $result = [];
        $i = 1;
        while ($name = $this->getValue('Teams', (string)$i)) {
            if ($name !== 'Freilos') {
                $result[] = new L98TeamModel($i, $name);
            }
            $i++;
        }
        return $result;
    }

    private function getSeason(): L98SeasonModel
    {
        $name = $this->getValue('Options', 'Name');
        return new L98SeasonModel($name);
    }

    public function parse(): L98SeasonModel
    {
        $season = $this->getSeason();
        foreach ($this->getTeams() as $team) {
            $season->addTeam($team);
        }
        foreach ($this->getMatchDays($this->indexById($season->getTeams())) as $matchDay) {
            $season->addMatchDay($matchDay);
        }

        return $season;
    }

    /**
     * @param L98TeamModel[] $teams
     * @return array
     */
    private function indexById(array $teams)
    {
        $result = [];
        foreach ($teams as $team) {
            $result[$team->getId()] = $team;
        }

        return $result;
    }
}