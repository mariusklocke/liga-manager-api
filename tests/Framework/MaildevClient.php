<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class MaildevClient
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private string $baseUrl;

    public function __construct()
    {
        $this->httpClient = new Psr18Client(HttpClient::create());
        $this->requestFactory = new Psr17Factory();

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
            return json_decode($response->getBody()->getContents());
        }

        return null;
    }
}
