<?php
/**
 * CollectionInterface.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Domain;

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
}