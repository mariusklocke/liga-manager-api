<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Event;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This event is emitted when a request has been received before it enters the middleware stack
 */
class RequestEvent
{
    private ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}
