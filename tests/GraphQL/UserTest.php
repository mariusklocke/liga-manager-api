<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Domain\User;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();
    }

    public function testListingUserRequiresAdminPermissions(): void
    {
        $this->client->clearAuth();

        $this->expectClientException();
        $this->client->getAllUsers();
    }

    public function testUserCanBeCreated()
    {
        $user = $this->getUserData();
        $this->client->createUser($user);

        $this->client->useCredentials($user['email'], $user['password']);
        $authenticatedUser = $this->client->getAuthenticatedUser();

        unset($user['password'], $user['team_ids']);
        self::assertEquals($user, (array)$authenticatedUser);
    }

    public function testPasswordResetSendsAnEmail()
    {
        self::getEmailClient()->deleteAllEmails();
        $user = $this->getUserData();
        $this->client->sendPasswordResetMail($user['email'], '/straight/to/hell');

        $tries = 0;
        do {
            usleep(100000);
            $emails = self::getEmailClient()->getAllEmails();
            $tries++;
        } while (count($emails) === 0 && $tries < 10);

        self::assertCount(1, $emails);
    }

    /**
     * @depends testUserCanBeCreated
     */
    public function testUserCanBeUpdated(): array
    {
        $user = $this->getUserData();
        $this->client->useCredentials($user['email'], $user['password']);

        $user['email'] = 'walter.white@example.com';
        $user['first_name'] = 'Walter';
        $user['last_name']  = 'White';
        $this->client->updateUser([
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ]);

        $this->client->useCredentials($user['email'], $user['password']);
        $authenticatedUser = (array)$this->client->getAuthenticatedUser();

        self::assertSame($user['email'], $authenticatedUser['email']);
        self::assertSame($user['first_name'], $authenticatedUser['first_name']);
        self::assertSame($user['last_name'], $authenticatedUser['last_name']);

        return $user;
    }

    /**
     * @depends testUserCanBeUpdated
     * @param array $user
     * @return array
     */
    public function testUserCanChangePassword(array $user): array
    {
        $this->client->useCredentials($user['email'], $user['password']);

        $user['password'] = 'foobar';
        $this->client->changeUserPassword($user['password']);

        $this->client->useCredentials($user['email'], $user['password']);
        $authenticatedUser = $this->client->getAuthenticatedUser();
        self::assertSame($authenticatedUser->id, $user['id']);
        self::assertSame($authenticatedUser->email, $user['email']);

        return $user;
    }

    /**
     * @depends testUserCanChangePassword
     * @param array $user
     */
    public function testUserCanBeDeleted(array $user)
    {
        $this->client->useCredentials($user['email'], $user['password']);
        self::assertNotNull($this->client->getAuthenticatedUser());

        $this->useAdminAuth();
        $this->client->deleteUser($user['id']);

        $this->client->useCredentials($user['email'], $user['password']);
        $this->expectClientException();
        $this->client->getAuthenticatedUser();
    }

    private function getUserData(): array
    {
        return [
            'id' => 'TeamManagerUserTest',
            'email' => 'user.test@example.com',
            'password' => '123456',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'role' => User::ROLE_TEAM_MANAGER,
            'team_ids' => []
        ];
    }
}