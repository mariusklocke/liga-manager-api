<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class CreateTeamCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $teamName;

    /**
     * @param string $teamName
     */
    public function __construct($teamName)
    {
        TypeAssert::assertString($teamName, 'teamName');
        $this->teamName = $teamName;
    }

    public function getTeamName() : string
    {
        return $this->teamName;
    }
}
