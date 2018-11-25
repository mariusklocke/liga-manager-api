<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\InputParser;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class MatchCommandController extends CommandController
{
    /**
     * @param string $matchId
     * @param Request $request
     * @return ResponseInterface
     */
    public function submitResult(string $matchId, Request $request): ResponseInterface
    {
        $homeScore = $request->getParsedBodyParam('home_score');
        $guestScore = $request->getParsedBodyParam('guest_score');
        $this->assertInteger('home_score', $homeScore);
        $this->assertInteger('guest_score', $guestScore);

        $command = new SubmitMatchResultCommand($matchId, $homeScore, $guestScore);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @param string $matchId
     * @return ResponseInterface
     */
    public function cancel(Request $request, string $matchId): ResponseInterface
    {
        $command = new CancelMatchCommand($matchId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param string $matchId
     * @param Request $request
     * @return ResponseInterface
     */
    public function schedule(string $matchId, Request $request): ResponseInterface
    {
        $kickoff = $request->getParsedBodyParam('kickoff');
        $this->assertString('kickoff', $kickoff);

        $this->commandBus->execute(new ScheduleMatchCommand($matchId, InputParser::parseDateTime($kickoff)));

        return $this->createResponse(204);
    }

    /**
     * @param string $matchId
     * @param Request $request
     * @return ResponseInterface
     */
    public function locate(string $matchId, Request $request): ResponseInterface
    {
        $pitchId = $request->getParsedBodyParam('pitch_id');
        $this->assertString('pitch_id', $pitchId);

        $this->commandBus->execute(new LocateMatchCommand($matchId, $pitchId));

        return $this->createResponse(204);
    }
}
