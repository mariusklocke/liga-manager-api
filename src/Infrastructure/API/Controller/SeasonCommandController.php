<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Slim\Http\Request;
use Slim\Http\Response;

class SeasonCommandController extends CommandController
{
    use DateParser;

    /**
     * @param Request $request
     * @return Response
     */
    public function createSeason(Request $request) : Response
    {
        $name = $request->getParsedBodyParam('name');
        if (!is_string($name) || mb_strlen($name) === 0 || mb_strlen($name) > 255) {
            throw new BadRequestException('Invalid season name');
        }
        $id = $this->commandBus->execute(new CreateSeasonCommand($name));
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param string $seasonId
     * @param Request $request
     * @return Response
     */
    public function createMatches(string $seasonId, Request $request) : Response
    {
        $startAt = $this->parseDate($request->getParsedBodyParam('start_at'));
        $this->commandBus->execute(new CreateMatchesForSeasonCommand($seasonId, $startAt));
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
     * @param string $seasonId
     * @param string $teamId
     * @return Response
     */
    public function addTeam(string $seasonId, string $teamId) : Response
    {
        $this->commandBus->execute(new AddTeamToSeasonCommand($seasonId, $teamId));
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
}
