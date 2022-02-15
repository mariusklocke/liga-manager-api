<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\Bus\BatchCommandBus;
use HexagonalPlayground\Application\Bus\CommandQueue;
use HexagonalPlayground\Application\Security\AuthContext;
use Psr\Http\Message\StreamInterface;

class Executor
{
    /** @var BatchCommandBus */
    private BatchCommandBus $commandBus;

    /**
     * @param BatchCommandBus $commandBus
     */
    public function __construct(BatchCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param StreamInterface $stream Stream containing the raw L98 ini data
     * @param AuthContext $authContext
     * @param TeamMapperInterface $teamMapper
     */
    public function __invoke(StreamInterface $stream, AuthContext $authContext, TeamMapperInterface $teamMapper): void
    {
        $parser = new L98IniParser($stream);
        $queue  = new CommandQueue();

        $seasonImporter = new SeasonImporter();
        $seasonId = $seasonImporter->import($parser, $queue);

        $teamImporter = new TeamImporter();
        $teamIdMap = $teamImporter->import($parser, $queue, $seasonId, $teamMapper);

        $matchImporter = new MatchImporter();
        $matchImporter->import($parser, $queue, $seasonId, $teamIdMap);

        $this->commandBus->execute($queue, $authContext);
    }
}