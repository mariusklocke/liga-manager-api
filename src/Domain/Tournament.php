<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\TournamentCreated;
use HexagonalPlayground\Domain\Util\Assert;

class Tournament extends Competition
{
    /** @var int */
    private $rounds;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        Assert::minLength($name, 1, "A tournament's name cannot be blank");
        Assert::maxLength($name, 255, "A tournament's name cannot exceed 255 characters");
        $this->setId($id);
        $this->name = $name;
        $this->matchDays = new ArrayCollection();
        $this->updateRoundCount();
        Publisher::getInstance()->publish(TournamentCreated::create($this->id));
    }

    /**
     * Clears all matches
     */
    public function clearMatches(): void
    {
        foreach ($this->matchDays as $matchDay) {
            /** @var MatchDay $matchDay */
            $matchDay->clearMatches();
        }
    }

    /**
     * Clears all matches for a given round
     *
     * @param int $round
     */
    public function clearMatchesForRound(int $round) : void
    {
        /** @var MatchDay[] $toRemove */
        $toRemove = $this->matchDays->filter(function (MatchDay $matchDay) use ($round) {
            return $matchDay->getNumber() === $round;
        });
        foreach ($toRemove as $matchDay) {
            $matchDay->clearMatches();
        }
        $this->updateRoundCount();
    }

    public function createMatchDay(int $number, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): MatchDay
    {
        $matchDay = parent::createMatchDay($number, $startDate, $endDate);
        $this->updateRoundCount();
        return $matchDay;
    }

    public function removeMatchDay(int $number): void
    {
        parent::removeMatchDay($number);
        $this->updateRoundCount();
    }

    /**
     * Updates the internal round counter
     */
    private function updateRoundCount() : void
    {
        $this->rounds = $this->matchDays->count();
    }
}