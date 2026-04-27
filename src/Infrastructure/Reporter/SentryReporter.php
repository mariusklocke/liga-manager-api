<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Reporter;

use Iterator;
use RuntimeException;
use Throwable;
use HexagonalPlayground\Application\ErrorReporter;
use HexagonalPlayground\Domain\Value\Uuid;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class SentryReporter implements ErrorReporter
{
    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param UriInterface $url
     * @param array $sdk
     */
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private UriInterface $url,
        private array $sdk = ["name" => "liga-manager-api", "version" => "1.0.0"],
    ) {}

    /**
     * @inheritDoc
     */
    public function report(Throwable $exception): void
    {
        if ($this->url->getScheme() === 'null') {
            throw new RuntimeException("Sentry URL is not configured");
        }

        $request = $this->requestFactory->createRequest("POST", $this->url);
        foreach ($this->generateHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        $request = $request->withBody($this->generateBody($exception));
        $response = $this->client->sendRequest($request);
        if ($response->getStatusCode() >= 400) {
            throw new RuntimeException("Sentry API error: {$response->getStatusCode()} {$response->getReasonPhrase()}");
        }
    }

    /**
     * Generate HTTP request headers
     *
     * @return Iterator<string, string>
     */
    private function generateHeaders(): Iterator
    {
        $token = $this->url->getUserInfo();
        yield "Content-Type" => "application/x-sentry-envelope";
        yield "X-Sentry-Auth" => "Sentry sentry_version=7, sentry_key={$token}, sentry_client={$this->sdk["name"]}/{$this->sdk["version"]}";
    }

    /**
     * Generate HTTP request body
     *
     * @param Throwable $exception
     * @return StreamInterface
     */
    private function generateBody(Throwable $exception): StreamInterface
    {
        $payload = $this->generatePayload($exception);
        $encodedPayload = json_encode($payload);
        $encodedItemHeader = json_encode([
            "type" => "event",
            "length" => \strlen($encodedPayload),
        ]);
        $encodedEnvelopeHeader = json_encode([
            "event_id" => $payload["event_id"],
            "dsn" => (string)$this->url,
            "sdk" => $this->sdk,
            "sent_at" => \date("c"),
        ]);

        return $this->streamFactory->createStream(
            implode("\n", [
                $encodedEnvelopeHeader,
                $encodedItemHeader,
                $encodedPayload,
            ]),
        );
    }

    /**
     * Generate HTTP request payload
     *
     * @param Throwable $exception
     * @return array
     */
    private function generatePayload(Throwable $exception): array
    {
        return [
            "event_id" => \str_replace("-", "", (string) Uuid::generate()),
            "timestamp" => \date("c"),
            "platform" => "php",
            "level" => "error",
            "exception" => [
                "values" => [$this->convertException($exception)],
            ],
        ];
    }

    /**
     * Convert exception to Sentry format
     *
     * @param Throwable $exception
     * @return array
     */
    private function convertException(Throwable $exception): array
    {
        return [
            "type" => \get_class($exception),
            "value" => $exception->getMessage(),
            "stacktrace" => [
                "frames" => \array_map(
                    fn(array $item) => [
                        "filename" => $item["file"],
                        "function" => $item["function"],
                        "lineno" => $item["line"],
                    ],
                    $exception->getTrace(),
                ),
            ],
        ];
    }
}
