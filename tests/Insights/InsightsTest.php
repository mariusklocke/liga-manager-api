<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Insights;

use HexagonalPlayground\Tests\Framework\HttpTest;

class InsightsTest extends HttpTest
{
    public function testInsightsCanBeQueried(): void
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Tests incompatible without xdebug');
        }
        $request = $this->createRequest('GET', '/api/_insights', [], ['REMOTE_ADDR' => '127.0.0.1']);
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $parsedBody = $this->parser->parse($response);

        self::assertObjectHasProperty('app', $parsedBody);
        self::assertObjectHasProperty('config', $parsedBody->app);
        self::assertObjectHasProperty('container', $parsedBody->app);
        self::assertObjectHasProperty('environment', $parsedBody->app);
        self::assertObjectHasProperty('packages', $parsedBody->app);

        self::assertObjectHasProperty('php', $parsedBody);
        self::assertObjectHasProperty('extensions', $parsedBody->php);
        self::assertObjectHasProperty('opcache', $parsedBody->php);
        self::assertObjectHasProperty('version', $parsedBody->php);
    }

    public function testInsightsAreRestrictedToLocalhost(): void
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Tests incompatible without xdebug');
        }
        $request = $this->createRequest('GET', '/api/_insights', [], ['REMOTE_ADDR' => '1.1.1.1']);
        $response = $this->sendRequest($request);
        self::assertSame(403, $response->getStatusCode());
    }
}
