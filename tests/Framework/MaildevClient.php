<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class MaildevClient implements EmailClientInterface
{
    /** @var Client */
    private $guzzle;

    public function __construct(string $baseUri)
    {
        $this->guzzle = new Client(['base_uri' => $baseUri]);
    }

    public function getAllEmails(): array
    {
        return $this->parseBody($this->guzzle->get('/email'));
    }

    public function deleteAllEmails(): void
    {
        $this->guzzle->delete('/email/all');
    }

    private function parseBody(ResponseInterface $response)
    {
        return json_decode((string)$response->getBody());
    }
}