<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class CreateTournamentCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $name;

    public function __construct(string $name)
    {
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