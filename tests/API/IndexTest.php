<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\API;

use HexagonalPlayground\Tests\Framework\HttpTest;

class IndexTest extends HttpTest
{
    public function testIndexCanBeFetched(): void
    {
        $request = $this->createRequest('GET', '/api');

        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $payload = $this->parser->parse($response);
        self::assertIsObject($payload);

        self::assertObjectHasProperty('limits', $payload);
        self::assertIsObject($payload->limits);
        self::assertObjectHasProperty('logos', $payload->limits);
        self::assertIsObject($payload->limits->logos);
        self::assertObjectHasProperty('size', $payload->limits->logos);
        self::assertIsInt($payload->limits->logos->size);
        self::assertGreaterThan(0, $payload->limits->logos->size);
        self::assertObjectHasProperty('types', $payload->limits->logos);
        self::assertIsArray($payload->limits->logos->types);
        self::assertGreaterThan(0, count($payload->limits->logos->types));
        self::assertObjectHasProperty('requests', $payload->limits);
        self::assertIsInt($payload->limits->requests);
        self::assertGreaterThan(0, $payload->limits->requests);
        self::assertObjectHasProperty('version', $payload);
        self::assertIsString($payload->version);
        self::assertGreaterThan(0, strlen($payload->version));
    }
}
