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
use HexagonalPlayground\Application\InputParser;
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
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);
        $id   = $this->commandBus->execute(new CreateSeasonCommand($name));
        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param string $seasonId
     * @param Request $request
     * @return ResponseInterface
     */
    public function createMatches(string $seasonId, Request $request): ResponseInterface
    {
        $matchDays = $request->getParsedBodyParam('dates');
        $this->assertArray('dates', $matchDays);

        $matchDays = array_map(function ($matchDay) {
            $this->assertArray('dates[]', $matchDay);
            return InputParser::parseDatePeriod($matchDay);
        }, $matchDays);

        $command = new CreateMatchesForSeasonCommand($seasonId, $matchDays);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function start(string $seasonId): ResponseInterface
    {
        $this->commandBus->execute(new StartSeasonCommand($seasonId));
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
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function delete(string $seasonId) : ResponseInterface
    {
        $this->commandBus->execute(new DeleteSeasonCommand($seasonId));
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
     * @param string $seasonId
     * @param string $teamId
     * @return ResponseInterface
     */
    public function removeTeam(string $seasonId, string $teamId): ResponseInterface
    {
        $this->commandBus->execute(new RemoveTeamFromSeasonCommand($seasonId, $teamId));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function addRankingPenalty(Request $request, string $seasonId): ResponseInterface
    {
        $teamId = $request->getParsedBodyParam('team_id');
        $reason = $request->getParsedBodyParam('reason');
        $points = $request->getParsedBodyParam('points');
        $this->assertString('team_id', $teamId);
        $this->assertString('reason', $reason);
        $this->assertInteger('points', $points);
        $command = new AddRankingPenaltyCommand($seasonId, $teamId, $reason, $points);
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
