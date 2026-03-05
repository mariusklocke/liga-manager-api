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
        $labels = [
            'status' => $event->getResponse()->getStatusCode(),
            'auth' => 'none'
        ];

        foreach ($event->getRequest()->getHeader('Authorization') as $value) {
            if (preg_match('/^bearer/i', $value)) {
                $labels['auth'] = 'bearer';
            } else if (preg_match('/^basic/i', $value)) {
                $labels['auth'] = 'basic';
            } else {
                $labels['auth'] = 'other';
            }
            break;
        }

        $this->metricsStore->add('php_requests', $labels);
        $this->metricsStore->set('php_memory_usage', (float)memory_get_usage());
        $this->metricsStore->set('php_memory_peak_usage', (float)memory_get_peak_usage());
    }

    public function handleQueryEvent(QueryEvent $event): void
    {
        $labels = [];
        if (preg_match('/^select/i', $event->getQuery())) {
            $labels['action'] = 'select';
        } else if (preg_match('/^insert/i', $event->getQuery())) {
            $labels['action'] = 'insert';
        } else if (preg_match('/^update/i', $event->getQuery())) {
            $labels['action'] = 'update';
        } else if (preg_match('/^delete/i', $event->getQuery())) {
            $labels['action'] = 'delete';
        } else {
            $labels['action'] = 'other';
        }
        $this->metricsStore->add('php_database_queries', $labels);
    }
}
