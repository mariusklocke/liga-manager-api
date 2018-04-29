<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityRepository;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;

class BaseRepository extends EntityRepository implements OrmRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $entity = parent::find($id, $lockMode, $lockVersion);
        if (null === $entity) {
            throw new NotFoundException(
                sprintf('Cannot find %s with ID %s', $this->stripNamespace($this->_class->getName()), $id)
            );
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity): void
    {
        $this->_em->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity): void
    {
        $this->_em->remove($entity);
    }

    /**
     * Strips the namespace part from a fully qualified class name
     *
     * @param string $className fully qualified class name
     * @return string
     */
    private function stripNamespace(string $className) : string
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}