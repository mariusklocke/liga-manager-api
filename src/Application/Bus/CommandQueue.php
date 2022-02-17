<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use ArrayIterator;
use HexagonalPlayground\Application\Command\CommandInterface;
use Iterator;

class CommandQueue
{
    /** @var array */
    private array $commands;

    public function __construct()
    {
        $this->commands = [];
    }

    public function add(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->commands);
    }

    public function size(): int
    {
        return count($this->commands);
    }
}
