<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

abstract class CreateSimpleEntityCommand implements CommandInterface
{
    use AuthenticationAware;
    use IdAware;

    /** @var string */
    private $name;

    /**
     * @param string|null $id
     * @param string      $name
     */
    public function __construct($id, $name)
    {
        $this->setId($id);
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