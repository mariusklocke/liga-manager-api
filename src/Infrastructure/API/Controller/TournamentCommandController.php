<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\Value\TeamIdPair;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class TournamentCommandController extends CommandController
{
    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function create(Request $request): ResponseInterface
    {
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);
        $id   = $this->commandBus->execute(new CreateTournamentCommand($name));
        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param string $id
     * @return ResponseInterface
     */
    public function delete(string $id): ResponseInterface
    {
        $this->commandBus->execute(new DeleteTournamentCommand($id));
        return $this->createResponse(204);
    }

    /**
     * @param string $tournamentId
     * @param int $round
     * @param Request $request
     * @return ResponseInterface
     */
    public function setRound(string $tournamentId, int $round, Request $request): ResponseInterface
    {
        $teamIdPairs = $request->getParsedBodyParam('team_pairs');
        $datePeriod  = $request->getParsedBodyParam('date_period');
        $this->assertArray('team_pairs', $teamIdPairs);
        $this->assertArray('date_period', $datePeriod);

        $teamIdPairs = array_map(function ($pair) {
            $this->assertArray('team_pairs[]', $pair);
            return TeamIdPair::fromArray($pair);
        }, $teamIdPairs);

        $datePeriod = InputParser::parseDatePeriod($datePeriod);

        $command = new SetTournamentRoundCommand($tournamentId, $round, $teamIdPairs, $datePeriod);
        $this->commandBus->execute($command);
        return $this->createResponse(204);
    }
}