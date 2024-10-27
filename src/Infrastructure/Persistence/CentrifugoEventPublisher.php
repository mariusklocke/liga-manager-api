<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Domain\Event\Event as DomainEvent;
use phpcent\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CentrifugoEventPublisher implements EventSubscriberInterface
{
    private Client $centrifugo;
    private LoggerInterface $logger;

    public function __construct(Client $centrifugo, LoggerInterface $logger)
    {
        $this->centrifugo = $centrifugo;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DomainEvent::class => 'onDomainEvent'
        ];
    }

    /**
     * Handles a DomainEvent by publishing it on a centrifugo channel
     *
     * @param DomainEvent $event
     */
    public function onDomainEvent(DomainEvent $event): void
    {
        $this->logger->debug('Publishing event to centrifugo', ['event' => $event]);
        $this->centrifugo->publish('events', $event->jsonSerialize());
    }
}
