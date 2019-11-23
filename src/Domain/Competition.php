<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Util\Assert;

abstract class Competition extends Entity
{
    /** @var string */
    protected $name;

    /** @var Collection */
    protected $matchDays;

    /**
     * @param int $number
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @return MatchDay
     */
    public function createMatchDay(int $number, DateTimeImmutable $startDate, DateTimeImmutable $endDate): MatchDay
    {
        Assert::false($this->matchDays->containsKey($number), 'Cannot create match day. Number already exists');
        $this->matchDays[$number] = new MatchDay($this, $number, $startDate, $endDate);

        return $this->matchDays[$number];
    }

    /**
     * @param int $number
     */
    public function removeMatchDay(int $number): void
    {
        $this->matchDays->remove($number);
    }
}