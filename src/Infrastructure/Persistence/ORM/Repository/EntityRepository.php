<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use Doctrine\Persistence\ObjectManager;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\EntityRepositoryInterface;
use HexagonalPlayground\Domain\Util\StringUtils;

abstract class EntityRepository implements EntityRepositoryInterface
{
    /** @var ObjectManager */
    protected ObjectManager $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return string
     */
    abstract protected static function getEntityClass(): string;

    /**
     * @param array $criteria
     * @return array
     */
    protected function findBy(array $criteria): array
    {
        return $this->manager->getRepository(static::getEntityClass())->findBy($criteria);
    }

    /**
     * @param array $criteria
     * @return object|null
     */
    protected function findOneBy(array $criteria): ?object
    {
        return $this->findBy($criteria)[0] ?? null;
    }

    /**
     * @param string $id
     * @return object
     * @throws NotFoundException
     */
    public function find(string $id): object
    {
        $type = StringUtils::stripNamespace(static::getEntityClass());

        return $this->get($id) ?? throw new NotFoundException('entityNotFound', [$type, $id]);
    }

    /**
     * @param string $id
     * @return object|null
     */
    public function get(string $id): ?object
    {
        return $this->manager->find(static::getEntityClass(), $id);
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->manager->getRepository(static::getEntityClass())->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity): void
    {
        $this->manager->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity): void
    {
        $this->manager->remove($entity);
    }

    public function flush(): void
    {
        $this->manager->flush();
    }
}
