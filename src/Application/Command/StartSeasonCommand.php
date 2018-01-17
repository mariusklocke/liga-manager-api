<?php

namespace HexagonalPlayground\Application\Command;

class StartSeasonCommand implements CommandInterface
{
    /** @var string */
    private $seasonId;

    public function __construct(string $seasonId)
    {
        $this->seasonId = $seasonId;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }
}