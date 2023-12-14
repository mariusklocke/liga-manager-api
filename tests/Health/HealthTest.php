<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Health;

use HexagonalPlayground\Tests\Framework\HttpTest;

class HealthTest extends HttpTest
{
    public function testHealthCheckIsSuccessful(): void
    {
        $request = $this->createRequest(
            'GET',
            '/api/health',
        );

        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $status = $this->parser->parse($response);
        self::assertIsObject($status);

        $checks = ['redis', 'doctrine'];

        foreach ($checks as $name) {
            self::assertTrue(property_exists($status, $name));
            self::assertEquals('OK', $status->{$name});
        }
    }
}
