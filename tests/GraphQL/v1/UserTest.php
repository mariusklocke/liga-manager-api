<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v1;

use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use Symfony\Component\Mailer\Event\MessageEvent;

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

        $exception = null;
        try {
            $this->client->getAllUsers();
        } catch (Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(Exception::class, $exception);

        $this->useAdminAuth();

        $users = $this->client->getAllUsers();

        $requiredAttributes = ['id', 'email', 'first_name', 'last_name', 'role', 'teams'];
        $sensitiveAttributes = ['password'];

        foreach ($users as $user) {
            foreach ($requiredAttributes as $requiredAttribute) {
                self::assertTrue(property_exists($user, $requiredAttribute));
            }
            foreach ($sensitiveAttributes as $sensitiveAttribute) {
                self::assertFalse(property_exists($user, $sensitiveAttribute));
            }
        }

        self::assertNotEmpty($users);
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
        $this->client->clearAuth();

        $user = $this->getUserData();
        $messageEvents = self::catchEvents(MessageEvent::class, function () use ($user) {
            $this->client->sendPasswordResetMail($user['email'], '/straight/to/hell');
        });

        self::assertCount(1, $messageEvents);
    }

    public function testPasswordResetDoesNotErrorWithUnknownEmail(): void
    {
        $messageEvents = self::catchEvents(MessageEvent::class, function () {
            $this->client->sendPasswordResetMail('mister.secret@example.com', '/nowhere');
        });

        self::assertCount(0, $messageEvents);
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
     * @return array
     */
    public function testSendingInviteEmail(array $user): array
    {
        $messageEvents = self::catchEvents(MessageEvent::class, function () use ($user) {
            $this->client->sendInviteMail($user['id'], '/straight/to/hell');
        });

        self::assertCount(1, $messageEvents);

        return $user;
    }

    /**
     * @depends testSendingInviteEmail
     * @param array $user
     * @return array
     */
    public function testAccessTokensCanBeInvalidated(array $user): array
    {
        $this->client->useCredentials($user['email'], $user['password']);
        self::assertNotNull($this->client->getAuthenticatedUser());

        $this->client->invalidateAccessTokens();

        // Test authentication by credentials still works
        self::assertNotNull($this->client->getAuthenticatedUser());

        return $user;
    }

    /**
     * @depends testAccessTokensCanBeInvalidated
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
            'id' => 'ed489246-cac2-4e67-8b22-ce2556d72a3e',
            'email' => 'user.test@example.com',
            'password' => '123456',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'role' => User::ROLE_TEAM_MANAGER,
            'team_ids' => []
        ];
    }
}
