<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\InputParser;
use Slim\Http\Request;

class MatchDayCommandController extends CommandController
{
    public function rescheduleMatchDay(string $matchDayId, Request $request)
    {
        $datePeriod = $request->getParsedBodyParam('date_period');
        $this->assertArray('date_period', $datePeriod);

        $command = new RescheduleMatchDayCommand($matchDayId, InputParser::parseDatePeriod($datePeriod));
        $this->commandBus->execute($command);
    }
}