<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class MatchDayCommandController extends CommandController
{
    public function rescheduleMatchDay(string $matchDayId, Request $request): ResponseInterface
    {
        $command = new RescheduleMatchDayCommand($matchDayId, $request->getParsedBodyParam('date_period'));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}