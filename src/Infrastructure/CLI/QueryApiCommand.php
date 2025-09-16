<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
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
        return $this->container->get(ClientInterface::class);
    }

    private function createRequest(InputInterface $input): RequestInterface
    {
        $method = strtoupper($input->getArgument('method'));
        $path   = $input->getArgument('path');

        if (!in_array($method, ['GET', 'POST', 'DELETE'])) {
            throw new Exception('Invalid HTTP method: ' . $method);
        }

        /** @var RequestFactoryInterface */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);
        $request = $requestFactory->createRequest($method, $path);

        if ($request->getMethod() === 'POST' && $input instanceof StreamableInputInterface) {
            /** @var StreamFactoryInterface */
            $streamFactory = $this->container->get(StreamFactoryInterface::class);
            $request = $request->withHeader('Content-Type', 'application/json');
            $data = stream_get_contents($input->getStream());
            $request = $request->withBody($streamFactory->createStream($data));
        }

        return $request;
    }
}