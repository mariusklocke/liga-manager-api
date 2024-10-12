<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Metrics;

use HexagonalPlayground\Tests\Framework\HttpTest;

class MetricsTest extends HttpTest
{
    public function testMetricsCanBeQueried(): void
    {
        $expectedMetrics = [
            ['name' => 'requests_total', 'type' => 'counter'],
            ['name' => 'requests_failed', 'type' => 'counter'],
            ['name' => 'requests_auth_none', 'type' => 'counter'],
            ['name' => 'requests_auth_jwt', 'type' => 'counter'],
            ['name' => 'requests_auth_basic', 'type' => 'counter'],
            ['name' => 'memory_usage', 'type' => 'gauge'],
            ['name' => 'memory_peak_usage', 'type' => 'gauge'],
            ['name' => 'database_queries', 'type' => 'counter']
        ];

        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $contentType = $response->getHeader('Content-Type')[0];
        self::assertStringStartsWith('text/plain', $contentType);
        $actualMetrics = (string)$response->getBody();

        foreach ($expectedMetrics as $metric) {
            self::assertMatchesRegularExpression(
                sprintf('/^%s \d+$/m', $metric['name']),
                $actualMetrics
            );
            self::assertMatchesRegularExpression(
                sprintf('/^# TYPE %s %s$/m', $metric['name'], $metric['type']),
                $actualMetrics
            );
        }
    }

    public function testMetricsRespectsMaintenanceMode(): void
    {
        $filePath = '.maintenance';
        touch($filePath);
        $response = $this->client->sendRequest(
            $this->createRequest('GET', '/api/metrics')
        );
        self::assertSame(503, $response->getStatusCode());
        unlink($filePath);
        $response = $this->client->sendRequest(
            $this->createRequest('GET', '/api/metrics')
        );
        self::assertSame(200, $response->getStatusCode());
    }
}
