<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class SeasonCommandController extends CommandController
{
    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function createSeason(Request $request): ResponseInterface
    {
        $command = new CreateSeasonCommand($request->getParsedBodyParam('name'));
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param string $seasonId
     * @param Request $request
     * @return ResponseInterface
     */
    public function createMatches(string $seasonId, Request $request): ResponseInterface
    {
        $command = new CreateMatchesForSeasonCommand($seasonId, $request->getParsedBodyParam('dates'));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function start(Request $request, string $seasonId): ResponseInterface
    {
        $command = new StartSeasonCommand($seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function end(Request $request, string $seasonId): ResponseInterface
    {
        $command = new EndSeasonCommand($seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function delete(Request $request, string $seasonId) : ResponseInterface
    {
        $command = new DeleteSeasonCommand($seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @param string $teamId
     * @return ResponseInterface
     */
    public function addTeam(Request $request, string $seasonId, string $teamId): ResponseInterface
    {
        $command = new AddTeamToSeasonCommand($seasonId, $teamId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @param string $teamId
     * @return ResponseInterface
     */
    public function removeTeam(Request $request, string $seasonId, string $teamId): ResponseInterface
    {
        $command = new RemoveTeamFromSeasonCommand($seasonId, $teamId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function addRankingPenalty(Request $request, string $seasonId): ResponseInterface
    {
        $command = new AddRankingPenaltyCommand(
            $seasonId,
            $request->getParsedBodyParam('team_id'),
            $request->getParsedBodyParam('reason'),
            $request->getParsedBodyParam('points')
        );
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @param string $rankingPenaltyId
     * @return ResponseInterface
     */
    public function removeRankingPenalty(Request $request, string $seasonId, string $rankingPenaltyId): ResponseInterface
    {
        $command = new RemoveRankingPenaltyCommand($rankingPenaltyId, $seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}
