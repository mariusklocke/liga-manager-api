<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use Psr\Http\Message\RequestInterface;

/**
 * Removes a trailing slash from request URI to simplify routing
 *
 * @link https://www.slimframework.com/docs/cookbook/route-patterns.html
 */
class RemoveTrailingSlash
{
    public function __invoke(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if ($path != '/' && substr($path, -1) == '/') {
            $uri = $uri->withPath(substr($path, 0, -1));
            return $request->withUri($uri);
        }

        return $request;
    }
}
