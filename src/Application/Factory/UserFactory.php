<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Application\IdGeneratorInterface;
use HexagonalPlayground\Domain\User;

class UserFactory extends EntityFactory
{
    /** @var callable */
    private $collectionFactory;

    public function __construct(IdGeneratorInterface $idGenerator, callable $collectionFactory)
    {
        parent::__construct($idGenerator);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param string $email
     * @param string $password
     * @return User
     */
    public function createUser(string $email, string $password)
    {
        return new User(
            $this->getIdGenerator()->generate(),
            $email,
            $password,
            call_user_func($this->collectionFactory)
        );
    }
}