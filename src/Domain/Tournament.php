<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class Tournament extends Competition
{
    /** @var int */
    private $rounds;

    /**
     * @param UuidGeneratorInterface $uuidGenerator
     * @param string $name
     * @param callable $collectionFactory
     */
    public function __construct(UuidGeneratorInterface $uuidGenerator, string $name, callable $collectionFactory)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->name = $name;
        $this->matches = $collectionFactory();
        $this->rounds = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function addMatch(Match $match)
    {
        $this->matches[] = $match;
    }

    /**
     * Clears all matches for a given round
     *
     * @param int $round
     */
    public function clearMatchesForRound(int $round)
    {
        $this->matches = $this->matches->filter(function (Match $match) use ($round) {
            return $match->getMatchDay() !== $round;
        });
    }
}