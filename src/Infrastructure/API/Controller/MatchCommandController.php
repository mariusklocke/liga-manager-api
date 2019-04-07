<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;
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
        $command = new SubmitMatchResultCommand(
            $matchId,
            $request->getParsedBodyParam('home_score'),
            $request->getParsedBodyParam('guest_score')
        );
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
        $command = new CancelMatchCommand($matchId, $request->getParsedBodyParam('reason'));
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
        TypeAssert::assertString($kickoff, 'kickoff');
        $command = new ScheduleMatchCommand($matchId, InputParser::parseDateTime($kickoff));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }

    /**
     * @param string $matchId
     * @param Request $request
     * @return ResponseInterface
     */
    public function locate(string $matchId, Request $request): ResponseInterface
    {
        $command = new LocateMatchCommand($matchId, $request->getParsedBodyParam('pitch_id'));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}
