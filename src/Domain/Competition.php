<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\UniquenessException;
use HexagonalPlayground\Domain\Util\Assert;

abstract class Competition extends Entity
{
    public const STATE_PREPARATION = 'preparation';
    public const STATE_PROGRESS = 'progress';
    public const STATE_ENDED = 'ended';

    /** @var string */
    protected string $name;

    /** @var Collection */
    protected Collection $matchDays;

    /** @var string */
    protected string $state;

    /**
     * @param string|null $id
     * @param int $number
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @return MatchDay
     */
    public function createMatchDay(?string $id, int $number, DateTimeImmutable $startDate, DateTimeImmutable $endDate): MatchDay
    {
        !$this->matchDays->containsKey($number) || throw new UniquenessException('matchDayNumberAlreadyExists');

        $this->matchDays[$number] = new MatchDay($id, $this, $number, $startDate, $endDate);

        return $this->matchDays[$number];
    }

    /**
     * @param int $number
     */
    public function removeMatchDay(int $number): void
    {
        $this->matchDays->remove($number);
    }

    /**
     * Initializes the competition
     */
    public function start(): void
    {
        !$this->hasStarted() || throw new ConflictException('competitionAlreadyStarted');
        $this->hasMatches() || throw new ConflictException('competitionHasNoMatches');
        $this->state = self::STATE_PROGRESS;
    }

    /**
     * Finalizes the competition
     */
    public function end(): void
    {
        $this->hasStarted() || throw new ConflictException('competitionNotStarted');
        $this->state = self::STATE_ENDED;
    }

    /**
     * @return bool
     */
    public function hasStarted() : bool
    {
        return ($this->state !== self::STATE_PREPARATION);
    }

    /**
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->state === self::STATE_PROGRESS;
    }

    /**
     * @return bool
     */
    public function hasMatches() : bool
    {
        return $this->matchDays->count() > 0;
    }
}
