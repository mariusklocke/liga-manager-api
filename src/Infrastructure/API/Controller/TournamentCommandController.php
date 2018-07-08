<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\InputParser;
use Slim\Http\Request;
use Slim\Http\Response;

class TournamentCommandController extends CommandController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request) : Response
    {
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);
        $id   = $this->commandBus->execute(new CreateTournamentCommand($name));
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param string $tournamentId
     * @param int $round
     * @param Request $request
     * @return Response
     */
    public function setRound(string $tournamentId, int $round, Request $request)
    {
        $teamIdPairs = $request->getParsedBodyParam('team_pairs');
        $plannedFor  = $request->getParsedBodyParam('planned_for');
        if (null !== $plannedFor) {
            $this->assertString('planned_for', $plannedFor);
            $plannedFor = InputParser::parseDateTime($plannedFor);
        }
        $this->assertArray('team_pairs', $teamIdPairs);

        if (empty($teamIdPairs)) {
            throw new InvalidInputException('Team pairs cannot be empty');
        }

        if (count($teamIdPairs) > 64) {
            throw new InvalidInputException('Request exceeds maximum amount of 64 team pairs.');
        }

        $command = new SetTournamentRoundCommand($tournamentId, $round, $teamIdPairs, $plannedFor);
        $this->commandBus->execute($command);
        return new Response(204);
    }
}