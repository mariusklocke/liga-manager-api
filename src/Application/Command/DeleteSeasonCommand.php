<?php

namespace HexagonalPlayground\Application\Command;

class DeleteSeasonCommand implements CommandInterface
{
    /** @var string */
    private $seasonId;

    public function __construct(string $seasonId)
    {
        $this->seasonId = $seasonId;
    }

    public function getSeasonId() : string
    {
        return $this->seasonId;
    }
}
