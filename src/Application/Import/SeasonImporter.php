<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\Bus\CommandQueue;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;

class SeasonImporter
{
    public function import(L98IniParser $parser, CommandQueue $queue): string
    {
        $name = $parser->getSectionValue('Options', 'Name');

        $command = new CreateSeasonCommand(null, $name);
        $queue->add($command);

        return $command->getId();
    }
}