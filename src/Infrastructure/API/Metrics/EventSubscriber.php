<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use HexagonalPlayground\Infrastructure\API\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    private StoreInterface $metricsStore;

    public function __construct(StoreInterface $metricsStore)
    {
        $this->metricsStore = $metricsStore;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => 'handleResponseEvent'
        ];
    }

    public function handleResponseEvent(ResponseEvent $event): void
    {
        $path = $event->getRequest()->getUri()->getPath();
        if ($path === '/api/health' || $path === '/api/metrics') {
            return;
        }

        $this->metricsStore->incrementCounter('requests_total');
        if ($event->getResponse()->getStatusCode() >= 400) {
            $this->metricsStore->incrementCounter('requests_failed');
        }

        $authHeader = $event->getRequest()->getHeader('Authorization')[0] ?? null;
        if ($authHeader === null) {
            $this->metricsStore->incrementCounter('requests_auth_none');
        } else if (preg_match('/^bearer/i', $authHeader)) {
            $this->metricsStore->incrementCounter('requests_auth_jwt');
        } else if (preg_match('/^basic/i', $authHeader)) {
            $this->metricsStore->incrementCounter('requests_auth_basic');
        }
    }
}
