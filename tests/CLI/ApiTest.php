<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use Symfony\Component\Console\Output\OutputInterface;
use HexagonalPlayground\Tests\Framework\CommandTest;

class ApiTest extends CommandTest
{
    public function testCanBeQueried(): void
    {
        // Valid GET request
        $result = $this->runCommand('app:api:query', ['method' => 'GET', 'path' => '/api/graphql']);
        self::assertExecutionSuccess($result);

        // Invalid GET Request
        $result = $this->runCommand('app:api:query', ['method' => 'GET', 'path' => '/non-existing']);
        self::assertExecutionSuccess($result);
        self::assertStringContainsString('ERR-NOT-FOUND', $result->output);

        // Valid POST request
        $body = [
            'query' => 'query allTeams {
              allTeams {
                id
              }
            }',
            'variables' => []
        ];
        $result = $this->runCommand('app:api:query', ['method' => 'POST', 'path' => '/api/graphql'], [json_encode($body)]);
        self::assertExecutionSuccess($result);

        // Invalid POST request
        $body = [
            'query' => ''
        ];
        $result = $this->runCommand('app:api:query', ['method' => 'POST', 'path' => '/api/graphql'], [json_encode($body)]);
        self::assertExecutionSuccess($result);
        self::assertStringContainsString('Syntax Error', $result->output);

        // Verbose output
        $result = $this->runCommand(
            'app:api:query',
            ['method' => 'GET', 'path' => '/api/graphql'],
            [],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );
        self::assertExecutionSuccess($result);
        self::assertMatchesRegularExpression('/HTTP\/\S+ 200 OK/', $result->output);

        // Very verbose output
        $result = $this->runCommand(
            'app:api:query',
            ['method' => 'GET', 'path' => '/api/graphql'],
            [],
            ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]
        );
        self::assertExecutionSuccess($result);
        self::assertMatchesRegularExpression('/HTTP\/\S+ 200 OK/', $result->output);
        self::assertMatchesRegularExpression('/Content-Length: \d+/i', $result->output);
    }
}