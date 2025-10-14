<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Exception;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;

class ApiClientFactory
{
    public function __invoke(ContainerInterface $container): ClientInterface
    {
        $url = getenv('APP_SERVER_INTERNAL') ?: throw new Exception('Missing value for environment variable APP_SERVER_INTERNAL');
        $parsedUrl = parse_url($url);
        switch ($parsedUrl['scheme']) {
            case 'fcgi':
                $scriptPath = join(DIRECTORY_SEPARATOR, [$container->get('app.home'), 'public', 'index.php']);
                $host       = $parsedUrl['host'] ?? '127.0.0.1';
                $port       = $parsedUrl['port'] ?? 9000;

                return new FastCgiClient($scriptPath, $host, $port);
            case 'http':
                return new Client(['base_uri' => $url]);
        }

        throw new Exception("Unsupported API server protocol: " . $parsedUrl['scheme']);
    }
}