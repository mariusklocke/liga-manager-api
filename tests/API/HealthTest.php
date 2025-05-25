<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\API;

use HexagonalPlayground\Tests\Framework\HttpTest;

class HealthTest extends HttpTest
{
    public function testHealthCheckIsSuccessful(): void
    {
        $request = $this->createRequest('GET', '/api/health');

        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $status = $this->parser->parse($response);
        self::assertIsObject($status);

        self::assertObjectHasProperty('version', $status);
        self::assertObjectHasProperty('checks', $status);

        $checks = ['redis', 'doctrine', 'email'];

        foreach ($checks as $name) {
            self::assertTrue(property_exists($status->checks, $name));
            self::assertEquals('OK', $status->checks->{$name});
        }
    }

    public function testHealthCheckRespectsMaintenanceMode(): void
    {
        $filePath = '.maintenance';
        touch($filePath);
        $request = $this->createRequest('GET', '/api/health');
        $response = $this->sendRequest($request);
        self::assertSame(503, $response->getStatusCode());

        unlink($filePath);
        $request = $this->createRequest('GET', '/api/health');
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
    }
}
