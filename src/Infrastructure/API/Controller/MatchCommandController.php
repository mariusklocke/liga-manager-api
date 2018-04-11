<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use Slim\Http\Request;
use Slim\Http\Response;

class MatchCommandController extends CommandController
{
    use DateParser;

    /**
     * @param string $matchId
     * @param Request $request
     * @return Response
     */
    public function submitResult(string $matchId, Request $request) : Response
    {
        $homeScore = $request->getParsedBodyParam('home_score');
        $guestScore = $request->getParsedBodyParam('guest_score');
        $this->assertTypeExact('home_score', $homeScore, 'integer');
        $this->assertTypeExact('guest_score', $guestScore, 'integer');

        $this->commandBus->execute(new SubmitMatchResultCommand($matchId, $homeScore, $guestScore));

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
        $kickoff = $this->parseDate($request->getParsedBodyParam('kickoff'));

        $this->commandBus->execute(new ScheduleMatchCommand($matchId, $kickoff));

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
        $this->assertTypeExact('pitch_id', $pitchId, 'string');

        $this->commandBus->execute(new LocateMatchCommand($matchId, $pitchId));

        return new Response(204);
    }
}
