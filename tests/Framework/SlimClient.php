<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class SlimClient
{
    private RequestHandlerInterface $app;
    private Psr17Factory $requestFactory;

    /**
     * @param RequestHandlerInterface $app
     */
    public function __construct(RequestHandlerInterface $app)
    {
        $this->app = $app;
        $this->requestFactory = new Psr17Factory();
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
        return $this->processRequest($this->createJsonRequest('GET', $uri, [], $headers));
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
        return $this->processRequest($this->createJsonRequest('POST', $uri, $bodyData, $headers));
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
        return $this->processRequest($this->createJsonRequest('DELETE', $uri, [], $headers));
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
        return $this->processRequest($this->createJsonRequest('PUT', $uri, $bodyData, $headers));
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
        return $this->processRequest($this->createJsonRequest('PATCH', $uri, $bodyData, $headers));
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
    private function createJsonRequest(string $method, string $uri, array $bodyData, array $headers = []) : ServerRequestInterface
    {
        $request = $this->requestFactory->createServerRequest($method, $uri);

        $headers['Content-Type'] = 'application/json';
        $request->getBody()->write(json_encode($bodyData));

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }

    public function sendUploadRequest(string $method, string $uri, string $filePath, string $fileMediaType, array $headers = []): ResponseInterface
    {
        $file = $this->requestFactory->createUploadedFile(
            $this->requestFactory->createStreamFromFile($filePath),
            filesize($filePath),
            0,
            basename($filePath),
            $fileMediaType
        );

        $request = $this->requestFactory->createServerRequest($method, $uri);
        $request = $request->withUploadedFiles(['file' => $file]);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $this->processRequest($request);
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
        $response = $this->app->handle($request);
        $output = ob_get_clean();
        if (strlen($output) > 0) {
            throw new RuntimeException(sprintf("Illegal output buffer content detected\n%s", $output));
        }
        return $response;
    }
}
