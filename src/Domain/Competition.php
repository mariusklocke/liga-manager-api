<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

abstract class Competition
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var CollectionInterface|Match[] */
    protected $matches;

    /**
     * Adds a match to the competition
     *
     * @param Match $match
     */
    abstract public function addMatch(Match $match);

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}