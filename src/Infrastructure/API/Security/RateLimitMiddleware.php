<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    private const APCU_KEY = 'requests';

    private int $maxRequests;

    private int $intervalSeconds;

    public function __construct(string $config)
    {
        $this->maxRequests = -1;
        $this->intervalSeconds = -1;
        $matches = [];
        if (preg_match('/^(\d+)r\/(\d+)s$/', $config, $matches)) {
            $this->maxRequests = (int)$matches[1];
            $this->intervalSeconds = (int)$matches[2];
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws RateLimitException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $this->getClientIp($request);

        if ($clientIp !== '' && $this->maxRequests > 0 && $this->intervalSeconds > 0) {
            $now = time();
            $minTime = $now - $this->intervalSeconds;
            $requestMap = apcu_fetch(self::APCU_KEY) ?: [];
            if (array_key_exists($clientIp, $requestMap)) {
                $requestMap[$clientIp] = array_filter($requestMap[$clientIp], function (int $requestTime) use ($minTime): bool {
                    return $requestTime >= $minTime;
                });
            } else {
                $requestMap[$clientIp] = [];
            }
            $requestMap[$clientIp][] = $now;
            if (count($requestMap[$clientIp]) > $this->maxRequests) {
                throw new RateLimitException(sprintf(
                    'Client %s exceeded rate limit of %d requests in %d seconds',
                    $clientIp,
                    $this->maxRequests,
                    $this->intervalSeconds
                ));
            }
            apcu_store(self::APCU_KEY, $requestMap);
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        return $request->getHeader('X-Forwarded-For')[0] ?? $request->getServerParams()['REMOTE_ADDR'] ?? '';
    }
}
