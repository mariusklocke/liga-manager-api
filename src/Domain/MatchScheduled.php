<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;

class MatchScheduled extends DomainEvent
{
    /**
     * @param string $matchId
     * @param DateTimeImmutable $kickoff
     * @return self
     */
    public static function create(string $matchId, DateTimeImmutable $kickoff): self
    {
        return self::createFromPayload([
            'matchId' => $matchId,
            'kickoff' => $kickoff->getTimestamp()
        ]);
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->payload['matchId'];
    }

    /**
     * @return DateTimeImmutable
     */
    public function getKickoff(): DateTimeImmutable
    {
        return new DateTimeImmutable('@' . $this->payload['kickoff']);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'match:scheduled';
    }
}