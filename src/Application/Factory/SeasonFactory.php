<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Application\IdGeneratorInterface;

class SeasonFactory extends EntityFactory
{
    /** @var callable */
    private $collectionFactory;

    /**
     * @param IdGeneratorInterface $idGenerator
     * @param callable $collectionFactory
     */
    public function __construct(IdGeneratorInterface $idGenerator, callable $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($idGenerator);
    }

    /**
     * @param string $name
     * @return Season
     */
    public function createSeason(string $name) : Season
    {
        return new Season($this->getIdGenerator()->generate(), $name);
    }
}
