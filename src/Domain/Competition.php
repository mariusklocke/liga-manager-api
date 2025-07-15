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
        Assert::false(
            $this->matchDays->containsKey($number),
            'Cannot create match day. Number already exists',
            UniquenessException::class
        );

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
        Assert::false(
            $this->hasStarted(),
            'Cannot start a competition which has already been started',
            ConflictException::class
        );
        Assert::true(
            $this->hasMatches(),
            'Cannot start a competition which has no matches',
            ConflictException::class
        );
        $this->state = self::STATE_PROGRESS;
    }

    /**
     * Finalizes the competition
     */
    public function end(): void
    {
        Assert::true(
            $this->hasStarted(),
            'Cannot end a competition which has not been started',
            ConflictException::class
        );
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
