<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;

class CreateMatchesForSeasonCommand implements CommandInterface
{
    /** @var string */
    private $seasonId;

    /** @var DateTimeImmutable */
    private $startAt;

    public function __construct(string $seasonId, DateTimeImmutable $startAt)
    {
        $this->seasonId = $seasonId;
        $this->startAt  = $startAt;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStartAt(): DateTimeImmutable
    {
        return $this->startAt;
    }
}
