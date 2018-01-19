<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

/**
 * PostLoad Event Listener to workaround Doctrine issue with null embeddables
 *
 * When loading entities which include embeddables, Doctrine instantiates these embeddables objects
 * even when it has never been set on the entity (all fields of embeddable are null)
 *
 * @link https://github.com/doctrine/doctrine2/issues/4568
 */
class DoctrineEmbeddableListener
{
    /** @var EntityManager */
    private $entityManager;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        $properties = array_keys($metadata->embeddedClasses);
        foreach ($properties as $property) {
            $reflectionProperty = $metadata->getReflectionProperty($property);
            $embeddable = $reflectionProperty->getValue($entity);
            if (is_object($embeddable) && $this->hasOnlyNullProperties($embeddable)) {
                $reflectionProperty->setValue($entity, null);
                $this->logger->debug(
                    sprintf('Nullified property %s of entity %s', $property, get_class($entity))
                );
            }
        }
    }

    /**
     * @param object $embeddable
     * @return bool
     */
    private function hasOnlyNullProperties($embeddable) : bool
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
