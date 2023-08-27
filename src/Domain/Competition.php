<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\UniquenessException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;

abstract class Competition extends Entity
{
    /** @var string */
    protected string $name;

    /** @var Collection */
    protected Collection $matchDays;

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
     * @param string $name
     */
    public function setName(string $name): void
    {
        Assert::true(
            StringUtils::length($name) > 0,
            "A competition's name cannot be blank",
            InvalidInputException::class
        );
        Assert::true(
            StringUtils::length($name) <= 255,
            "A competition's name cannot exceed 255 characters",
            InvalidInputException::class
        );
        $this->name = $name;
    }
}
