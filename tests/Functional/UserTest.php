<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Tests\Functional\Framework\ApiException;
use HexagonalPlayground\Tests\Functional\Framework\EmailClientInterface;
use HexagonalPlayground\Tests\Functional\Framework\MaildevClient;

class UserTest extends TestCase
{
    /** @var EmailClientInterface */
    private $emailClient;

    public function setUp()
    {
        parent::setUp();
        $this->emailClient = new MaildevClient(Environment::get('MAILDEV_URI'));
    }

    public function testPasswordResetSendsAnEmail()
    {
        $this->emailClient->deleteAllEmails();
        $this->client->sendPasswordResetMail('user3@example.com', '/straight/to/hell');

        $tries = 0;
        do {
            usleep(100000);
            $emails = $this->emailClient->getAllEmails();
            $tries++;
        } while (count($emails) === 0 && $tries < 10);

        self::assertCount(1, $emails);
    }

    public function testListingUserRequiresAdminPermissions()
    {
        $this->client->setBasicAuth('user1@example.com', '123456');
        $exception = null;
        try {
            $this->client->getAllUsers();
        } catch (ApiException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ApiException::class, $exception);
        self::assertEquals(403, $exception->getCode());

        $this->client->setBasicAuth('admin@example.com', '123456');
        $users = $this->client->getAllUsers();
        self::assertNotEmpty($users);
    }

    public function testUserCanBeAuthenticated()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $user = $this->client->getAuthenticatedUser();
        self::assertObjectHasAttribute('email', $user);
        self::assertEquals('admin@example.com', $user->email);
    }

    /**
     * @return string
     */
    public function testUserCanBeCreated()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $user = $this->client->createUser([
            'email' => 'nobody@example.com',
            'password' => 'secret',
            'first_name' => 'My Name Is',
            'last_name' => 'Nobody',
            'role' => 'team_manager',
            'teams' => []
        ]);
        self::assertResponseHasValidId($user);
        $user = $this->client->getAuthenticatedUser();
        self::assertResponseHasValidId($user);

        return $user->id;
    }

    /**
     * @depends testUserCanBeCreated
     */
    public function testUserCanChangePassword()
    {
        $this->client->setBasicAuth('nobody@example.com', 'secret');
        $this->client->changePassword('even_more_secret');
        try {
            $this->client->getAuthenticatedUser();
        } catch (ApiException $exception) {
            // nothing
        }
        self::assertNotNull($exception);

        $this->client->setBasicAuth('nobody@example.com', 'even_more_secret');
        $user = $this->client->getAuthenticatedUser();
        self::assertObjectHasAttribute('email', $user);
        self::assertEquals('nobody@example.com', $user->email);
    }

    public function testUserCanBeDeleted()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $user = $this->client->createUser([
            'email' => 'anybody@example.com',
            'password' => 'secret',
            'first_name' => 'My Name Is',
            'last_name' => 'Anybody',
            'role' => 'team_manager',
            'teams' => []
        ]);
        self::assertResponseHasValidId($user);

        $exception = null;
        try {
            $this->client->deleteUser($user->id);
        } catch (ApiException $e) {
            $exception = $e;
        }

        self::assertNull($exception);
    }

    public function testUserCanBeUpdated()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $teamA = $this->client->createTeam('Team A');
        self::assertResponseHasValidId($teamA);
        $user = $this->client->createUser([
            'email' => 'who@example.com',
            'password' => 'secret',
            'first_name' => 'My Name Is',
            'last_name' => 'Who',
            'role' => 'admin',
            'teams' => [$teamA->id]
        ]);
        self::assertResponseHasValidId($user);

        $teamB = $this->client->createTeam('Team B');
        self::assertResponseHasValidId($teamB);
        $this->client->updateUser($user->id, [
            'first_name' => 'Homer',
            'last_name'  => 'Simpson',
            'email'      => 'homer.simpson@example.com',
            'role'       => 'team_manager',
            'teams'      => [$teamB->id]
        ]);

        $this->client->setBasicAuth('homer.simpson@example.com', 'secret');
        $user = $this->client->getAuthenticatedUser();
        self::assertObjectHasAttribute('email', $user);
        self::assertObjectHasAttribute('first_name', $user);
        self::assertObjectHasAttribute('last_name', $user);

        self::assertEquals('Homer', $user->first_name);
        self::assertEquals('Simpson', $user->last_name);
        self::assertEquals('homer.simpson@example.com', $user->email);
        self::assertEquals('team_manager', $user->role);
    }
}