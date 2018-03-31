<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
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
        if (!is_int($homeScore) || $homeScore < 0 || $homeScore > 99) {
            throw new BadRequestException('Invalid home score');
        }
        if (!is_int($guestScore) || $guestScore < 0 || $homeScore > 99) {
            throw new BadRequestException('Invalid guest score');
        }

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
        $kickoffString = $request->getParsedBodyParam('kickoff');
        try {
            $kickoff = new DateTimeImmutable($kickoffString);
        } catch (\Exception $e) {
            throw new BadRequestException('Invalid kickoff date format');
        }

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
        if (!is_string($pitchId)) {
            throw new BadRequestException('Parameter "pitch_id" has to be a string');
        }

        $this->commandBus->execute(new LocateMatchCommand($matchId, $pitchId));

        return new Response(204);
    }
}
