<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use HexagonalPlayground\Infrastructure\API\Event\ResponseEvent;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\QueryEvent;
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
            ResponseEvent::class => 'handleResponseEvent',
            QueryEvent::class => 'handleQueryEvent'
        ];
    }

    public function handleResponseEvent(ResponseEvent $event): void
    {
        $this->metricsStore->add('php_requests_total');
        if ($event->getResponse()->getStatusCode() >= 400) {
            $this->metricsStore->add('php_requests_failed');
        }

        $authHeader = $event->getRequest()->getHeader('Authorization')[0] ?? null;
        if ($authHeader === null) {
            $this->metricsStore->add('php_requests_auth_none');
        } else if (preg_match('/^bearer/i', $authHeader)) {
            $this->metricsStore->add('php_requests_auth_jwt');
        } else if (preg_match('/^basic/i', $authHeader)) {
            $this->metricsStore->add('php_requests_auth_basic');
        }

        $this->metricsStore->set('php_memory_usage', (float)memory_get_usage());
        $this->metricsStore->set('php_memory_peak_usage', (float)memory_get_peak_usage());
    }

    public function handleQueryEvent(QueryEvent $event): void
    {
        $this->metricsStore->add('php_database_queries');
    }
}
