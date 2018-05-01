<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use DateTimeImmutable;
use HexagonalPlayground\Domain\DomainEvent;

class EventSerializer
{
    /** @var callable[] */
    private $eventFactories;

    public function __construct(array $eventFactories)
    {
        $this->eventFactories = $eventFactories;
    }

    /**
     * @param DomainEvent $event
     * @return object
     */
    public function serialize(DomainEvent $event): object
    {
        $object             = new \stdClass();
        $object->id         = $event->getId();
        $object->occurredAt = $event->getOccurredAt()->getTimestamp();
        $object->payload    = $event->getPayload();
        $object->type       = $event->getName();
        return $object;
    }

    /**
     * @param object $serialized
     * @return DomainEvent
     */
    public function deserialize(object $serialized): DomainEvent
    {
        $factory    = $this->getFactory($serialized->type);
        $id         = (string) $serialized->id;
        $occurredAt = new DateTimeImmutable('@' . $serialized->occurredAt);
        $payload    = (array) $serialized->payload;
        return $factory($id, $occurredAt, $payload);
    }

    /**
     * @param string $type
     * @return callable
     */
    private function getFactory(string $type): callable
    {
        if (!isset($this->eventFactories[$type])) {
            throw new \InvalidArgumentException("Unknown event type: " . $type);
        }
        return $this->eventFactories[$type];
    }
}