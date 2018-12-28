<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class CreateTournamentCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        TypeAssert::assertString($name, 'name');
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}