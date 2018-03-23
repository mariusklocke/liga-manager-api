<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
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
        if (!is_string($name) || mb_strlen($name) < 1 || mb_strlen($name) > 255) {
            return $this->createBadRequestResponse('Invalid parameter "name"');
        }

        $id = $this->commandBus->execute(new CreateTournamentCommand($name));
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
        $teamIdPairs = $request->getParsedBody();
        if (!is_array($teamIdPairs)) {
            return $this->createBadRequestResponse(
                sprintf('Request body has to be array, %s given', gettype($teamIdPairs))
            );
        }

        if (count($teamIdPairs) < 1 || count($teamIdPairs) > 64) {
            return $this->createBadRequestResponse(
                sprintf('Expected amount of team pairs between and 1 and 64, %s given', count($teamIdPairs))
            );
        }

        $command = new SetTournamentRoundCommand($tournamentId, $round);
        foreach ($teamIdPairs as $pair) {
            if (!is_string($pair['home_team_id']) || !is_string($pair['guest_team_id'])) {
                return $this->createBadRequestResponse('Team IDs have to be of type string');
            }
            $command->addPair($pair['home_team_id'], $pair['guest_team_id']);
        }

        $this->commandBus->execute($command);
        return new Response(204);
    }
}