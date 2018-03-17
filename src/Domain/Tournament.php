<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class Tournament extends Competition
{
    /** @var int */
    private $rounds;

    /**
     * @param string $id
     * @param string $name
     * @param CollectionInterface|Match[] $matches
     */
    public function __construct(string $id, string $name, $matches)
    {
        $this->id = $id;
        $this->name = $name;
        $this->matches = $matches;
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
        $this->matches = $this->matches->filter(function (Match $match) use ($round) {
            return $match->getMatchDay() !== $round;
        });
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