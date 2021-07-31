<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\WebAuthn;

use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Tests\Framework\HttpTest;

class AuthTest extends HttpTest
{
    public function testCredentialCreationOptionsCanBeRequested(): void
    {
        $request = $this->createRequest(
            'POST',
            '/api/webauthn/credential/options',
        );
        $response = $this->client->sendRequest($request);
        self::assertSame(401, $response->getStatusCode());

        $request = $this->authenticator->withAdminAuth($request);

        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $options = $this->parser->parse($response);
        self::assertIsObject($options);
        self::assertObjectHasAttribute('challenge', $options);
        self::assertObjectHasAttribute('user', $options);
        self::assertIsObject($options->user);
        self::assertObjectHasAttribute('id', $options->user);
    }

    public function testLoginOptionsCanBeRequested(): void
    {
        $request = $this->createRequest(
            'POST',
            '/api/webauthn/login/options',
            ['email' => Environment::get('ADMIN_EMAIL')]
        );

        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $options = $this->parser->parse($response);
        self::assertIsObject($options);
        self::assertObjectHasAttribute('challenge', $options);
        self::assertObjectHasAttribute('timeout', $options);
    }

    public function testCredentialsCanBeFound(): void
    {
        $request = $this->createRequest(
            'GET',
            '/api/webauthn/credential'
        );
        $request = $this->authenticator->withAdminAuth($request);

        $response = $this->client->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());

        $credentials = $this->parser->parse($response);
        self::assertIsArray($credentials);
        self::assertCount(0, $credentials);
    }
}
