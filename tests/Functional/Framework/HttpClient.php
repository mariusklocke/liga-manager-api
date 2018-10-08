<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

class HttpClient
{
    /** @var App */
    private $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Send a GET Request
     *
     * @param string $uri
     * @param array $headers
     * @return ResponseInterface
     */
    public function get(string $uri, array $headers = []) : ResponseInterface
    {
        return $this->processRequest($this->createRequest('GET', $uri, [], $headers));
    }

    /**
     * Send a POST Request
     *
     * @param string $uri
     * @param array $bodyData
     * @param array $headers
     * @return ResponseInterface
     */
    public function post(string $uri, array $bodyData = [], array $headers = []) : ResponseInterface
    {
        return $this->processRequest($this->createRequest('POST', $uri, $bodyData, $headers));
    }

    /**
     * Send a DELETE Request
     *
     * @param string $uri
     * @param array $headers
     * @return ResponseInterface
     */
    public function delete(string $uri, array $headers = []) : ResponseInterface
    {
        return $this->processRequest($this->createRequest('DELETE', $uri, [], $headers));
    }

    /**
     * Send a PUT Request
     *
     * @param string $uri
     * @param array $bodyData
     * @param array $headers
     * @return ResponseInterface
     */
    public function put(string $uri, array $bodyData = [], array $headers = []) : ResponseInterface
    {
        return $this->processRequest($this->createRequest('PUT', $uri, $bodyData, $headers));
    }

    /**
     * Send a PATCH Request
     *
     * @param string $uri
     * @param array $bodyData
     * @param array $headers
     * @return ResponseInterface
     */
    public function patch(string $uri, array $bodyData = [], array $headers = []): ResponseInterface
    {
        return $this->processRequest($this->createRequest('PATCH', $uri, $bodyData, $headers));
    }

    /**
     * Parse the response body data
     *
     * @param StreamInterface $body
     * @return mixed
     */
    public function parseBody(StreamInterface $body)
    {
        $body->rewind();
        $data = $body->getContents();
        return json_decode($data);
    }

    /**
     * Create a request object
     *
     * @param string $method
     * @param string $uri
     * @param array $bodyData
     * @param array $headers
     * @return ServerRequestInterface
     */
    private function createRequest(string $method, string $uri, array $bodyData, array $headers = []) : ServerRequestInterface
    {
        $body = new Body(fopen('php://memory', 'w+'));
        $body->write(json_encode($bodyData));
        $body->rewind();
        $headers['Content-Type'] = 'application/json';
        $request = new Request($method, Uri::createFromString($uri), new Headers($headers), [], [], $body);

        return $request;
    }

    /**
     * Trigger the application to process a request and return a response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function processRequest(ServerRequestInterface $request) : ResponseInterface
    {
        ob_start();
        $response = $this->app->process($request, new Response());
        $output = ob_get_clean();
        if (strlen($output) > 0) {
            throw new RuntimeException(sprintf("Illegal output buffer content detected\n%s", $output));
        }
        return $response;
    }
}