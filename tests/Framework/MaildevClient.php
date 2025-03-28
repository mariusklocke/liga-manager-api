<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class MaildevClient
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private string $baseUrl;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->requestFactory = new HttpFactory();

        $url = parse_url(getenv('EMAIL_URL'));
        $this->baseUrl = 'http://' . $url['host'] . ':1080';
    }

    public function getMails(): array
    {
        return $this->request('GET', '/email');
    }

    public function deleteMails(): void
    {
        $this->request('DELETE', '/email/all');
    }

    private function request(string $method, string $endpoint)
    {
        $request  = $this->requestFactory->createRequest($method, $this->baseUrl . $endpoint);
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw new \Exception('Request to maildev has failed');
        }

        if (str_starts_with(current($response->getHeader('Content-Type')), 'application/json')) {
            return json_decode((string)$response->getBody());
        }

        return null;
    }
}
