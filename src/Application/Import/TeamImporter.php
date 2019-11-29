<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use Generator;
use HexagonalPlayground\Application\Bus\CommandQueue;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;

class TeamImporter
{
    public function import(L98IniParser $parser, CommandQueue $queue, string $seasonId, TeamMapperInterface $mapper): array
    {
        $idMap = [];
        foreach ($this->getTeams($parser) as $foreignId => $name) {
            if (!is_string($name) || $name === 'Freilos') {
                continue;
            }

            $id = $mapper->map($name);
            if (null === $id) {
                $command = new CreateTeamCommand(null, $name);
                $queue->add($command);
                $id = $command->getId();
            }

            $idMap[$foreignId] = $id;

            $queue->add(new AddTeamToSeasonCommand($seasonId, $id));
        }
        return $idMap;
    }

    private function getTeams(L98IniParser $parser): Generator
    {
        $i = 1;
        while ($name = $parser->getSectionValue('Teams', (string)$i)) {
            yield $i => $name;
            $i++;
        }
    }
}
