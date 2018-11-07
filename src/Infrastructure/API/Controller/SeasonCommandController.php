<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\InputParser;
use Slim\Http\Request;
use Slim\Http\Response;

class SeasonCommandController extends CommandController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function createSeason(Request $request) : Response
    {
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);
        $id   = $this->commandBus->execute(new CreateSeasonCommand($name));
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param string $seasonId
     * @param Request $request
     * @return Response
     */
    public function createMatches(string $seasonId, Request $request) : Response
    {
        $matchDays = $request->getParsedBodyParam('dates');
        $this->assertArray('dates', $matchDays);

        $matchDays = array_map(function ($matchDay) {
            $this->assertArray('dates[]', $matchDay);
            return InputParser::parseDatePeriod($matchDay);
        }, $matchDays);

        $command = new CreateMatchesForSeasonCommand($seasonId, $matchDays);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function start(string $seasonId) : Response
    {
        $this->commandBus->execute(new StartSeasonCommand($seasonId));
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function delete(string $seasonId) : Response
    {
        $this->commandBus->execute(new DeleteSeasonCommand($seasonId));
        return new Response(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @param string $teamId
     * @return Response
     */
    public function addTeam(Request $request, string $seasonId, string $teamId) : Response
    {
        $command = new AddTeamToSeasonCommand($seasonId, $teamId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @param string $teamId
     * @return Response
     */
    public function removeTeam(string $seasonId, string $teamId) : Response
    {
        $this->commandBus->execute(new RemoveTeamFromSeasonCommand($seasonId, $teamId));
        return new Response(204);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @return Response
     */
    public function addRankingPenalty(Request $request, string $seasonId): Response
    {
        $teamId = $request->getParsedBodyParam('team_id');
        $reason = $request->getParsedBodyParam('reason');
        $points = $request->getParsedBodyParam('points');
        $this->assertString('team_id', $teamId);
        $this->assertString('reason', $reason);
        $this->assertInteger('points', $points);
        $command = new AddRankingPenaltyCommand($seasonId, $teamId, $reason, $points);
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param Request $request
     * @param string $seasonId
     * @param string $rankingPenaltyId
     * @return Response
     */
    public function removeRankingPenalty(Request $request, string $seasonId, string $rankingPenaltyId): Response
    {
        $command = new RemoveRankingPenaltyCommand($rankingPenaltyId, $seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return new Response(204);
    }
}
