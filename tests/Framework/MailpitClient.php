<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use stdClass;

class MailpitClient
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private string $baseUrl;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->requestFactory = new HttpFactory();

        $url = parse_url(getenv('EMAIL_URL'));
        $this->baseUrl = 'http://' . $url['host'] . ':8025';
    }

    public function getMail(string $id): stdClass
    {
        return $this->request('GET', "/api/v1/message/{$id}");
    }

    public function listMails(): array
    {
        $data = $this->request('GET', '/api/v1/messages');

        return $data->messages;
    }

    public function deleteMails(): void
    {
        $this->request('DELETE', '/api/v1/messages');
    }

    private function request(string $method, string $endpoint)
    {
        $request  = $this->requestFactory->createRequest($method, $this->baseUrl . $endpoint);
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw new \Exception("{$method} {$endpoint} to mailpit has failed: {$response->getStatusCode()} {$response->getReasonPhrase()}");
        }

        if (str_starts_with(current($response->getHeader('Content-Type')), 'application/json')) {
            return json_decode((string)$response->getBody());
        }

        return null;
    }
}