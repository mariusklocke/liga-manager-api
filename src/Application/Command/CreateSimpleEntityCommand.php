<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

abstract class CreateSimpleEntityCommand implements CommandInterface
{
    use IdAware;

    /** @var string */
    private $name;

    /**
     * @param string|null $id
     * @param string      $name
     */
    public function __construct(?string $id, string $name)
    {
        $this->setId($id);
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
