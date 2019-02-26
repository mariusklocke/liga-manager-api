<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;
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
        $command = new CreateTournamentCommand($request->getParsedBodyParam('id'), $request->getParsedBodyParam('name'));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(200, ['id' => $command->getId()]);
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
        $datePeriod = $request->getParsedBodyParam('date_period');
        TypeAssert::assertArray($datePeriod, 'date_period');
        $datePeriod = InputParser::parseDatePeriod($datePeriod);

        $teamIdPairs = $request->getParsedBodyParam('team_pairs');
        TypeAssert::assertArray($teamIdPairs, 'team_pairs');
        $teamIdPairs = array_map(function ($teamIdPair) {
            TypeAssert::assertArray($teamIdPair, 'team_pairs[]');
            return TeamIdPair::fromArray($teamIdPair);
        }, $teamIdPairs);

        $command = new SetTournamentRoundCommand(
            $tournamentId,
            $round,
            $teamIdPairs,
            $datePeriod
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }
}