<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class DeleteSeasonCommand implements CommandInterface
{
    /** @var string */
    private string $seasonId;

    /**
     * @param string $seasonId
     */
    public function __construct(string $seasonId)
    {
        $this->seasonId = $seasonId;
    }

    /**
     * @return string
     */
    public function getSeasonId() : string
    {
        return $this->seasonId;
    }
}
