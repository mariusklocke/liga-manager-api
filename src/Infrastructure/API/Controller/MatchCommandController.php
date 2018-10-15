<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\InputParser;
use Slim\Http\Request;
use Slim\Http\Response;

class MatchCommandController extends CommandController
{
    /**
     * @param string $matchId
     * @param Request $request
     * @return Response
     */
    public function submitResult(string $matchId, Request $request) : Response
    {
        $homeScore = $request->getParsedBodyParam('home_score');
        $guestScore = $request->getParsedBodyParam('guest_score');
        $this->assertInteger('home_score', $homeScore);
        $this->assertInteger('guest_score', $guestScore);

        $command = new SubmitMatchResultCommand($matchId, $homeScore, $guestScore);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return new Response(204);
    }

    /**
     * @param string $matchId
     * @return Response
     */
    public function cancel(string $matchId) : Response
    {
        $this->commandBus->execute(new CancelMatchCommand($matchId));
        return new Response(204);
    }

    /**
     * @param string $matchId
     * @param Request $request
     * @return Response
     */
    public function schedule(string $matchId, Request $request) : Response
    {
        $kickoff = $request->getParsedBodyParam('kickoff');
        $this->assertString('kickoff', $kickoff);

        $this->commandBus->execute(new ScheduleMatchCommand($matchId, InputParser::parseDateTime($kickoff)));

        return new Response(204);
    }

    /**
     * @param string $matchId
     * @param Request $request
     * @return Response
     */
    public function locate(string $matchId, Request $request) : Response
    {
        $pitchId = $request->getParsedBodyParam('pitch_id');
        $this->assertString('pitch_id', $pitchId);

        $this->commandBus->execute(new LocateMatchCommand($matchId, $pitchId));

        return new Response(204);
    }
}
