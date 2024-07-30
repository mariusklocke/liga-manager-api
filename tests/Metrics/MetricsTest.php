<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Metrics;

use HexagonalPlayground\Tests\Framework\HttpTest;

class MetricsTest extends HttpTest
{
    public function testMetricsCanBeQueried(): void
    {
        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $contentType = $response->getHeader('Content-Type')[0];
        self::assertSame('text/plain', $contentType);
        $metrics = (string)$response->getBody();

        // Request metrics
        self::assertMatchesRegularExpression('/^requests_total \d+$/m', $metrics);
        self::assertMatchesRegularExpression('/^requests_failed \d+$/m', $metrics);
        self::assertMatchesRegularExpression('/^requests_auth_none \d+$/m', $metrics);
        self::assertMatchesRegularExpression('/^requests_auth_jwt \d+$/m', $metrics);
        self::assertMatchesRegularExpression('/^requests_auth_basic \d+$/m', $metrics);

        // Memory metrics
        self::assertMatchesRegularExpression('/^memory_usage \d+$/m', $metrics);
        self::assertMatchesRegularExpression('/^memory_peak_usage \d+$/m', $metrics);

        // Database metrics
        self::assertMatchesRegularExpression('/^database_queries \d+$/m', $metrics);
    }
}
