<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\Tournament;
use HexagonalPlayground\Domain\IdGeneratorInterface;

class TournamentFactory extends EntityFactory
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
     * @return Tournament
     */
    public function createTournament(string $name) : Tournament
    {
        return new Tournament($this->getIdGenerator()->generate(), $name, $this->collectionFactory);
    }
}