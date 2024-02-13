<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ResponseSerializer
{
    private StreamFactoryInterface $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param ResponseInterface $response
     * @param string $data
     * @return ResponseInterface
     */
    public function serializeHtml(ResponseInterface $response, string $data): ResponseInterface
    {
        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withBody($this->streamFactory->createStream($data));
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $data
     * @return ResponseInterface
     */
    public function serializeJson(ResponseInterface $response, mixed $data): ResponseInterface
    {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
    }
}
