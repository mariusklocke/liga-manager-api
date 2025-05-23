<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Metrics;

use HexagonalPlayground\Tests\Framework\HttpTest;

class MetricsTest extends HttpTest
{
    public function testMetricsCanBeQueried(): void
    {
        $expectedMetrics = [
            ['name' => 'php_requests_total', 'type' => 'counter'],
            ['name' => 'php_requests_failed', 'type' => 'counter'],
            ['name' => 'php_requests_auth_none', 'type' => 'counter'],
            ['name' => 'php_requests_auth_jwt', 'type' => 'counter'],
            ['name' => 'php_requests_auth_basic', 'type' => 'counter'],
            ['name' => 'php_memory_usage', 'type' => 'gauge'],
            ['name' => 'php_memory_peak_usage', 'type' => 'gauge'],
            ['name' => 'php_database_queries', 'type' => 'counter']
        ];

        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->client->sendRequest($request);
        $this->schemaValidator->validateResponse($request, $response);
        self::assertSame(200, $response->getStatusCode());
        $contentType = $response->getHeader('Content-Type')[0];
        self::assertStringStartsWith('text/plain', $contentType);
        $actualMetrics = (string)$response->getBody();

        foreach ($expectedMetrics as $metric) {
            self::assertMatchesRegularExpression(
                sprintf('/^# TYPE %s %s$/m', $metric['name'], $metric['type']),
                $actualMetrics
            );
            if ($metric['type'] === 'gauge') {
                $pattern = '/^%s [\d.e+]+$/m';
            } else {
                $pattern = '/^%s \d+$/m';
            }
            self::assertMatchesRegularExpression(
                sprintf($pattern, $metric['name']),
                $actualMetrics
            );
        }
    }

    public function testMetricsRespectsMaintenanceMode(): void
    {
        $filePath = '.maintenance';
        touch($filePath);
        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->client->sendRequest($request);
        $this->schemaValidator->validateResponse($request, $response);
        self::assertSame(503, $response->getStatusCode());

        unlink($filePath);
        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->client->sendRequest($request);
        $this->schemaValidator->validateResponse($request, $response);
        self::assertSame(200, $response->getStatusCode());
    }
}
