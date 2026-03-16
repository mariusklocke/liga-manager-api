<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Metrics;

use HexagonalPlayground\Tests\Framework\HttpTest;

class MetricsTest extends HttpTest
{
    public function testMetricsCanBeQueried(): void
    {
        $expectedPatterns = [
            '/^# TYPE php_requests counter$/m',
            '/^# TYPE php_memory_usage gauge$/m',
            '/^# TYPE php_memory_peak_usage gauge$/m',
            '/^# TYPE php_database_queries counter$/m',
            '/^php_requests{auth="\S+",status="\d+"} \d+$/m',
            '/^php_memory_usage [\d.e+]+$/m',
            '/^php_memory_peak_usage [\d.e+]+$/m',
            '/^php_database_queries{action="\S+"} \d+$/m',
        ];

        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $contentType = $response->getHeader('Content-Type')[0];
        self::assertStringStartsWith('text/plain', $contentType);
        $actualMetrics = (string)$response->getBody();

        foreach ($expectedPatterns as $pattern) {
            self::assertMatchesRegularExpression($pattern, $actualMetrics);
        }
    }

    public function testMetricsRespectsMaintenanceMode(): void
    {
        $filePath = '.maintenance';
        touch($filePath);
        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->sendRequest($request);
        self::assertSame(503, $response->getStatusCode());

        unlink($filePath);
        $request = $this->createRequest('GET', '/api/metrics');
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
    }
}
