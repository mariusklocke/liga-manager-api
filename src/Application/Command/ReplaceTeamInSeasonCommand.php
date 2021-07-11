<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class ReplaceTeamInSeasonCommand implements CommandInterface
{
    /** @var string */
    private $seasonId;
    /** @var string */
    private $currentTeamId;
    /** @var string */
    private $replacementTeamId;

    /**
     * @param string $seasonId
     * @param string $currentTeamId
     * @param string $replacementTeamId
     */
    public function __construct($seasonId, $currentTeamId, $replacementTeamId)
    {
        TypeAssert::assertString($seasonId, 'seasonId');
        TypeAssert::assertString($currentTeamId, 'currentTeamId');
        TypeAssert::assertString($replacementTeamId, 'replacementTeamId');
        $this->seasonId = $seasonId;
        $this->currentTeamId = $currentTeamId;
        $this->replacementTeamId = $replacementTeamId;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return string
     */
    public function getCurrentTeamId(): string
    {
        return $this->currentTeamId;
    }

    /**
     * @return string
     */
    public function getReplacementTeamId(): string
    {
        return $this->replacementTeamId;
    }

    
}
