<?php

namespace HexagonalDream\Infrastructure\API\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware which removes a trailing slash from request URI to simplify routing
 *
 * @link https://www.slimframework.com/docs/cookbook/route-patterns.html
 */
class RemoveTrailingSlash
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if ($path != '/' && substr($path, -1) == '/') {
            $uri = $uri->withPath(substr($path, 0, -1));
            return $next($request->withUri($uri), $response);
        }

        return $next($request, $response);
    }
}
