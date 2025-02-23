<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use GuzzleHttp\Client;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class QueryApiCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:api:query');
        $this->setDescription('Sends an arbirary request to the API');
        $this->addArgument('path', InputArgument::REQUIRED, 'URL path');
        $this->addOption('headers', null, InputOption::VALUE_NONE, 'Show response headers in output');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client  = $this->createClient();
        $request = new Request('GET', $input->getArgument('path'));

        $response = $client->sendRequest($request);

        if ($input->getOption('headers')) {
            foreach ($response->getHeaders() as $name => $values) {
                $output->writeln($name . ': ' . implode(', ', $values));
            }
            $output->writeln('');
        }

        $output->writeln((string)$response->getBody());

        return 0;
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
}