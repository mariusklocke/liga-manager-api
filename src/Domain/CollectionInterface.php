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

    /**
     * Remove the given element from the collection by searching
     *
     * @param mixed $element
     */
    public function removeElement($element);

    /**
     * Checks whether an element is contained in the collection.
     *
     * @param mixed $element The element to search for.
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     */
    public function contains($element);
}