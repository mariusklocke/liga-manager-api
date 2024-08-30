<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PsrSlimClient implements ClientInterface
{
    private RequestHandlerInterface $app;

    /**
     * @param RequestHandlerInterface $app
     */
    public function __construct(RequestHandlerInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (!$request instanceof ServerRequestInterface) {
            throw new PsrClientException('Request is not instance of "Psr\Http\Message\ServerRequestInterface"');
        }

        ob_start();
        $response = $this->app->handle($request);
        $output = ob_get_clean();
        if (strlen($output) > 0) {
            throw new PsrClientException(sprintf("Illegal output buffer content detected\n%s", $output));
        }

        return $response;
    }
}
