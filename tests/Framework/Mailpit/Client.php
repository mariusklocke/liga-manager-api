<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\Mailpit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class Client
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private string $baseUrl;

    public function __construct()
    {
        $this->httpClient = new GuzzleClient();
        $this->requestFactory = new HttpFactory();

        $url = parse_url(getenv('EMAIL_URL'));
        $this->baseUrl = 'http://' . $url['host'] . ':8025';
    }

    /**
     * @return Message[]
     */
    public function listMessages(): array
    {
        $result = [];
        $data = $this->request('GET', '/api/v1/messages');
        foreach ($data->messages as $message) {
            $message = $this->request('GET', "/api/v1/message/{$message->ID}");
            $result[] = new Message(
                $message->ID,
                new Address($message->From->Address, $message->From->Name),
                array_map(fn($to) => new Address($to->Address, $to->Name), $message->To),
                $message->Subject,
                $message->Text,
                $message->HTML
            );
        }

        return $result;
    }

    public function deleteMessages(): void
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