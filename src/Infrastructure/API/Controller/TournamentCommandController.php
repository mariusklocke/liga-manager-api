<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
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
        $body = $request->getParsedBody();
        $this->assertTypeExact('body', $body, 'array');
        if (!isset($body['team_pairs'])) {
            // Sending team pairs directly in body is deprecated
            // Keep this for compatibility
            $teamIdPairs = $body;
            $plannedFor  = null;
        } else {
            $teamIdPairs = $request->getParsedBodyParam('team_pairs');
            $plannedFor  = $request->getParsedBodyParam('planned_for');
            $this->assertTypeExact('team_pairs', $teamIdPairs, 'array');
            $this->assertTypeExact('planned_for', $plannedFor, 'string');
            $plannedFor = $this->parseDate($plannedFor);
        }

        if (count($teamIdPairs) < 1 || count($teamIdPairs) > 64) {
            throw new BadRequestException(
                sprintf('Expected amount of team pairs between and 1 and 64, %s given', count($teamIdPairs))
            );
        }

        $command = new SetTournamentRoundCommand($tournamentId, $round, $plannedFor);
        foreach ($teamIdPairs as $pair) {
            $this->assertTypeExact('home_team_id', $pair['home_team_id'], 'string');
            $this->assertTypeExact('guest_team_id', $pair['guest_team_id'], 'string');
            $command->addPair($pair['home_team_id'], $pair['guest_team_id']);
        }

        $this->commandBus->execute($command);
        return new Response(204);
    }
}