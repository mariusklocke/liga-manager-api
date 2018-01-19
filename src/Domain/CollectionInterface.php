<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface CollectionInterface extends Countable, IteratorAggregate, ArrayAccess
{
    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray();

    /**
     * Clears the collection, removing all elements.
     */
    public function clear();
}