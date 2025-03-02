<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\Requests\DeleteRequest;
use hollodotme\FastCGI\Requests\GetRequest;
use hollodotme\FastCGI\Requests\PostRequest;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FastCgiClient implements ClientInterface
{
    private Client $client;
    private ConfiguresSocketConnection $connection;
    private string $scriptPath;

    public function __construct(string $scriptPath, string $host, int $port = 9000)
    {
        $this->client = new Client();
        $this->connection = new NetworkSocket($host, $port);
        $this->scriptPath = $scriptPath;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->mapResponse(
            $this->client->sendRequest($this->connection, $this->mapRequest($request))
        );
    }

    private function mapRequest(RequestInterface $request): ProvidesRequestData
    {
        switch ($request->getMethod()) {
            case 'GET':
                $result = new GetRequest($this->scriptPath, '');
                break;
            case 'POST':
                $result = new PostRequest($this->scriptPath, (string)$request->getBody());
                $contentType = $request->getHeader('Content-Type')[0] ?? null;
                if ($contentType) {
                    $result->setContentType($contentType);
                }
                break;
            case 'DELETE':
                $result = new DeleteRequest($this->scriptPath, '');
                break;
            default:
                throw new Exception("Unsupported HTTP method: {$request->getMethod()}");
        }

        $result->setRequestUri($request->getUri()->getPath());
        $result->setRemoteAddress('127.0.0.1');

        return $result;
    }

    private function mapResponse(ProvidesResponseData $input): ResponseInterface
    {
        $status = $input->getHeader('status')[0] ?? 200;

        return new Response((int)$status, $input->getHeaders(), $input->getBody());
    }
}