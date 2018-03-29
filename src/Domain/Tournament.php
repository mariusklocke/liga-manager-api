<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;

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
        $this->id = $id;
        $this->name = $name;
        $this->matches = new ArrayCollection();
        $this->updateRoundCount();
    }

    /**
     * {@inheritdoc}
     */
    public function addMatch(Match $match) : void
    {
        $this->matches[] = $match;
        if ($match->getMatchDay() > $this->rounds) {
            $this->rounds = $match->getMatchDay();
        }
    }

    /**
     * Clears all matches for a given round
     *
     * @param int $round
     */
    public function clearMatchesForRound(int $round) : void
    {
        $toRemove = $this->matches->filter(function (Match $match) use ($round) {
            return $match->getMatchDay() === $round;
        });
        foreach ($toRemove->getKeys() as $key) {
            $this->matches->remove($key);
        }
        $this->updateRoundCount();
    }

    /**
     * Updates the internal round counter
     */
    private function updateRoundCount() : void
    {
        $this->rounds = 0;
        foreach ($this->matches as $match) {
            $this->rounds = max($this->rounds, $match->getMatchDay());
        }
    }
}