<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use PHPUnit\Framework\Attributes\Depends;

class UserTest extends TestCase
{
    private static array $userData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();
    }

    public static function setupBeforeClass(): void
    {
        self::$userData = [
            'id' => DataGenerator::generateId(),
            'email' => DataGenerator::generateEmail(),
            'password' => DataGenerator::generatePassword(),
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'role' => User::ROLE_TEAM_MANAGER,
            'team_ids' => []
        ];
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
        $user = self::$userData;
        $this->client->createUser($user);

        $this->client->useCredentials($user['email'], $user['password']);
        $authenticatedUser = $this->client->getAuthenticatedUser();

        unset($user['password'], $user['team_ids']);
        self::assertEquals($user, (array)$authenticatedUser);
    }

    public function testPasswordResetSendsAnEmail()
    {
        $this->mailClient->deleteMails();
        $this->client->clearAuth();
        $user = self::$userData;
        $this->client->sendPasswordResetMail($user['email'], '/straight/to/hell');
        sleep(5);
        $mails = $this->mailClient->getMails();
        self::assertCount(1, $mails);
        $mail = $mails[0];
        self::assertIsObject($mail);
        $recipients = $mail->to;
        self::assertCount(1, $recipients);
        $recipient = current($recipients);
        self::assertIsObject($recipient);
        self::assertEquals($user['email'], $recipient->address);
    }

    public function testPasswordResetDoesNotErrorWithUnknownEmail(): void
    {
        $this->mailClient->deleteMails();
        $recipient = 'mister.secret@example.com';
        $this->client->sendPasswordResetMail($recipient, '/nowhere');
        sleep(5);
        $mails = $this->mailClient->getMails();
        self::assertCount(0, $mails);
    }

    #[Depends("testUserCanBeCreated")]
    public function testUserCanBeUpdated(): array
    {
        $user = self::$userData;
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
     * @param array $user
     * @return array
     */
    #[Depends("testUserCanBeUpdated")]
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
     * @param array $user
     * @return array
     */
    #[Depends("testUserCanChangePassword")]
    public function testSendingInviteEmail(array $user): array
    {
        $this->mailClient->deleteMails();
        $this->client->sendInviteMail($user['id'], '/straight/to/hell');
        sleep(5);
        $mails = $this->mailClient->getMails();
        self::assertCount(1, $mails);
        $mail = $mails[0];
        self::assertIsObject($mail);
        $recipients = $mail->to;
        self::assertCount(1, $recipients);
        $recipient = current($recipients);
        self::assertIsObject($recipient);
        self::assertEquals($user['email'], $recipient->address);

        return $user;
    }

    /**
     * @param array $user
     * @return array
     */
    #[Depends("testSendingInviteEmail")]
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
     * @param array $user
     */
    #[Depends("testAccessTokensCanBeInvalidated")]
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
}
