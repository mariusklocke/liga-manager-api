<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use Slim\Http\Request;
use Slim\Http\Response;

class TournamentCommandController extends CommandController
{
    use DateParser;

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request) : Response
    {
        $name = $request->getParsedBodyParam('name');
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
        $plannedFor  = $this->parseDate($request->getParsedBodyParam('planned_for'));

        $command = new SetTournamentRoundCommand($tournamentId, $round, $plannedFor);
        foreach ($teamIdPairs as $pair) {
            $command->addPair($pair['home_team_id'], $pair['guest_team_id']);
        }

        $this->commandBus->execute($command);
        return new Response(204);
    }
}