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
     * @param IdGeneratorInterface $uuidGenerator
     * @param callable $collectionFactory
     */
    public function __construct(IdGeneratorInterface $uuidGenerator, callable $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($uuidGenerator);
    }

    /**
     * @param string $name
     * @return Season
     */
    public function createSeason(string $name) : Season
    {
        return new Season($this->getIdGenerator()->generate(), $name, $this->collectionFactory);
    }
}
