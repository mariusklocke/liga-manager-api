<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class CreateTeamCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $teamName;

    public function __construct(string $teamName)
    {
        $this->teamName = $teamName;
    }

    public function getTeamName() : string
    {
        return $this->teamName;
    }
}
