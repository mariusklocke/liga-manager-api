<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTime;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\EventList;
use Iterator;

class EventTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testEventsCanBeListed(array $filter): void
    {
        $response = self::$client->request(new EventList([
            'filter' => $filter,
            'pagination' => [
                'limit' => 50,
                'offset' => 0
            ]
        ]));

        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('eventList', $response->data);
        self::assertIsArray($response->data->eventList);
        self::assertNotEmpty($response->data->eventList);

        foreach ($response->data->eventList as $event) {
            self::assertObjectHasAttribute('id', $event);
            self::assertObjectHasAttribute('occurredAt', $event);
            self::assertObjectHasAttribute('type', $event);

            $occurredAt = new DateTime($event->occurredAt);
            if (isset($filter['occurredAfter'])) {
                $occurredAfter = new DateTime($filter['occurredAfter']);
                self::assertTrue($occurredAt >= $occurredAfter);
            }

            if (isset($filter['occurredBefore'])) {
                $occurredBefore = new DateTime($filter['occurredBefore']);
                self::assertTrue($occurredAt <= $occurredBefore);
            }
        }
    }

    public function filterProvider(): Iterator
    {
        yield 'empty filter' => [[]];
        yield 'simple filter' => [[
            'occurredAfter' => self::formatDate(new DateTime('yesterday'))
        ]];
        yield 'complete filter' => [[
            'occurredAfter' => self::formatDate(new DateTime('yesterday')),
            'occurredBefore' => self::formatDate(new DateTime('tomorrow')),
            'types' => ['season:created']
        ]];
    }
}
