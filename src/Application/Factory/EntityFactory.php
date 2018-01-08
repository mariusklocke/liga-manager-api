<?php
/**
 * EntityFactory.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Application\Factory;

use HexagonalDream\Domain\UuidGeneratorInterface;

abstract class EntityFactory
{
    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @return UuidGeneratorInterface
     */
    protected function getIdGenerator()
    {
        return $this->uuidGenerator;
    }
}