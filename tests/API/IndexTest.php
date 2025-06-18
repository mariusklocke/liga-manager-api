<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\API;

use HexagonalPlayground\Tests\Framework\HttpTest;

class IndexTest extends HttpTest
{
    public function testIndexCanBeFetched(): void
    {
        $request = $this->createRequest('GET', '/api');

        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $payload = $this->parser->parse($response);
        self::assertIsObject($payload);

        // allowed file types
        self::assertObjectHasProperty('allowed_file_types', $payload);
        self::assertIsArray($payload->allowed_file_types);
        self::assertGreaterThan(0, count($payload->allowed_file_types));
        foreach ($payload->allowed_file_types as $type) {
            self::assertIsString($type);
        }

        // max file size
        self::assertObjectHasProperty('max_file_size', $payload);
        self::assertIsInt($payload->max_file_size);
        self::assertGreaterThan(0, $payload->max_file_size);

        // max requests
        self::assertObjectHasProperty('max_requests', $payload);
        self::assertIsInt($payload->max_requests);
        self::assertGreaterThan(0, $payload->max_requests);

        // version
        self::assertObjectHasProperty('version', $payload);
        self::assertIsString($payload->version);
        self::assertGreaterThan(0, strlen($payload->version));
    }
}
