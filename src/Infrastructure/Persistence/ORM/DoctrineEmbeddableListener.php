<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

/**
 * PostLoad Event Listener to workaround Doctrine issue with null embeddables
 *
 * When loading entities which include embeddables, Doctrine instantiates these embeddables objects
 * even when it has never been set on the entity (all fields of embeddable are null)
 * This listener hooks into loading of entities and removes embeddables where all fields are null
 *
 * @link https://github.com/doctrine/doctrine2/issues/4568
 */
class DoctrineEmbeddableListener
{
    /** @var EntityManager */
    private EntityManager $entityManager;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function postLoad(PostLoadEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $entityClass = get_class($entity);
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $properties = array_keys($metadata->embeddedClasses);
        foreach ($properties as $property) {
            $reflectionProperty = $metadata->getReflectionProperty($property);
            $embeddable = $reflectionProperty->getValue($entity);
            if (is_object($embeddable) && $this->hasOnlyNullProperties($embeddable)) {
                $reflectionProperty->setValue($entity, null);
                $this->logger->debug("Nullified property $property of entity $entityClass");
            }
        }
    }

    /**
     * @param object $embeddable
     * @return bool
     */
    private function hasOnlyNullProperties(object $embeddable) : bool
    {
        /** @var ReflectionProperty[] $properties */
        $properties = $this->entityManager->getClassMetadata(get_class($embeddable))->getReflectionProperties();
        foreach ($properties as $property) {
            if (null !== $property->getValue($embeddable)) {
                return false;
            }
        }
        return true;
    }
}
