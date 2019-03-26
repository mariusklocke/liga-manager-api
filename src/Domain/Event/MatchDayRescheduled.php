<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use DateTimeImmutable;

class MatchDayRescheduled extends Event
{
    private const DATE_FORMAT = 'Y-m-d';

    public static function create(string $matchDayId, DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        return self::createFromPayload([
            'matchDayId' => $matchDayId,
            'datePeriod' => [
                'from' => $startDate->format(self::DATE_FORMAT),
                'to'   => $endDate->format(self::DATE_FORMAT)
            ]
        ]);
    }
}