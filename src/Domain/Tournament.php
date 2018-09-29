<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\Uuid;

class Tournament extends Competition
{
    /** @var int */
    private $rounds;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        Assert::minLength($name, 1, "A tournament's name cannot be blank");
        Assert::maxLength($name, 255, "A tournament's name cannot exceed 255 characters");
        $this->id = Uuid::create();
        $this->name = $name;
        $this->matchDays = new ArrayCollection();
        $this->updateRoundCount();
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
        foreach ($toRemove as $matchDays) {
            $matchDays->clearMatches();
        }
        $this->updateRoundCount();
    }

    /**
     * @param int $round
     * @param Match[] $matches
     * @throws DomainException
     */
    public function setMatchesForRound(int $round, array $matches): void
    {
        if (!isset($this->matchDays[$round])) {
            $this->matchDays[$round] = new MatchDay($this, $round, new \DateTimeImmutable(), new \DateTimeImmutable());
        }

        $this->matchDays[$round]->clearMatches();
        foreach ($matches as $match) {
            $this->matchDays[$round]->addMatch($match);
        }
        $this->updateRoundCount();
    }

    public function setMatchDay(MatchDay $matchDay): void
    {
        $this->matchDays[$matchDay->getNumber()] = $matchDay;
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