<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\StreamableInputInterface;

class QueryApiCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:api:query');
        $this->setDescription('Sends an arbirary request to the API');
        $this->addArgument('method', InputArgument::REQUIRED, 'HTTP method (GET, POST, DELETE)');
        $this->addArgument('path', InputArgument::REQUIRED, 'URL path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client   = $this->createClient();
        $request  = $this->createRequest($input);
        $response = $client->sendRequest($request);

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Status: ' . $response->getStatusCode());
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            foreach ($response->getHeaders() as $name => $values) {
                if ($name !== 'Status') {
                    $output->writeln($name . ': ' . implode(', ', $values));
                }
            }
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('');
        }

        $output->writeln((string)$response->getBody());

        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300 ? 0 : 1;
    }

    private function createClient(): ClientInterface
    {
        $url = getenv('APP_SERVER_INTERNAL');
        if (!$url) {
            throw new Exception('Missing value for environment variable APP_SERVER_INTERNAL');
        }

        $parsedUrl = parse_url($url);
        switch ($parsedUrl['scheme']) {
            case 'fcgi':
                $appHome    = $this->container->get('app.home');
                $scriptPath = join(DIRECTORY_SEPARATOR, [$appHome, 'public', 'index.php']);
                $host       = $parsedUrl['host'];
                $port       = $parsedUrl['port'] ?? 9000;

                return new FastCgiClient($scriptPath, $host, $port);
            case 'http':
                return new Client(['base_uri' => $url]);
        }

        throw new Exception("Unsupported API server protocol: " . $parsedUrl['scheme']);
    }

    private function createRequest(InputInterface $input): RequestInterface
    {
        $method = strtoupper($input->getArgument('method'));
        $path   = $input->getArgument('path');

        if (!in_array($method, ['GET', 'POST', 'DELETE'])) {
            throw new Exception('Invalid HTTP method: ' . $method);
        }
        
        $request = new Request($method, $path);

        if ($request->getMethod() === 'POST' && $input instanceof StreamableInputInterface) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $data = stream_get_contents($input->getStream());
            $request = $request->withBody(Utils::streamFor($data));
        }

        return $request;
    }
}