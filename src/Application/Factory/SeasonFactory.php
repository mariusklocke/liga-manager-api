<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\UuidGeneratorInterface;

class SeasonFactory extends EntityFactory
{
    /** @var callable */
    private $collectionFactory;

    /**
     * @param UuidGeneratorInterface $uuidGenerator
     * @param callable $collectionFactory
     */
    public function __construct(UuidGeneratorInterface $uuidGenerator, callable $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($uuidGenerator);
    }

    /**
     * @param string $name
     * @return Season
     */
    public function createSeason(string $name)
    {
        return new Season($this->getIdGenerator(), $name, $this->collectionFactory);
    }
}
