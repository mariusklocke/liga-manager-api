<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
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
        $command = new CreateTournamentCommand($request->getParsedBodyParam('name'));
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return ResponseInterface
     */
    public function delete(Request $request, string $id): ResponseInterface
    {
        $command = new DeleteTournamentCommand($id);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
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
        $command = new SetTournamentRoundCommand(
            $tournamentId,
            $round,
            $request->getParsedBodyParam('team_pairs'),
            $request->getParsedBodyParam('date_period')
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }
}