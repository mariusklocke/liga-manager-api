<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class MatchDayCommandController extends CommandController
{
    public function rescheduleMatchDay(string $matchDayId, Request $request): ResponseInterface
    {
        $datePeriod = $request->getParsedBodyParam('date_period');
        TypeAssert::assertArray($datePeriod, 'date_period');
        $datePeriod = InputParser::parseDatePeriod($datePeriod);

        $command = new RescheduleMatchDayCommand($matchDayId, $datePeriod);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}
