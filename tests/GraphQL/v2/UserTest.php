<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\GraphQL\Auth;
use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateTeam;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateUser;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteUser;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\InvalidateAccessTokens;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\SendInviteMail;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\SendPasswordResetMail;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateUser;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateUserPassword;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\User;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\UserList;

class UserTest extends TestCase
{
    public function testUserCanBeCreated(): object
    {
        $id = DataGenerator::generateId();
        $email = DataGenerator::generateEmail();
        $password = DataGenerator::generatePassword();
        $role = 'team_manager';
        $firstName = DataGenerator::generateString(8);
        $lastName = DataGenerator::generateString(8);
        $teamIds = [$this->createTeam()];

        self::assertNull($this->getUser($id, $this->defaultAdminAuth));

        self::$client->request(new CreateUser([
            'id' => $id,
            'email' => $email,
            'password' => $password,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'role' => $role,
            'teamIds' => $teamIds
        ]), $this->defaultAdminAuth);

        sleep(1); // Workaround for issue "Password has changed after token has been issued"
        $userAuth = self::$client->authenticate(new BasicAuth($email, $password));
        $user = $this->getUser($id, $userAuth);
        self::assertIsObject($user);
        self::assertEquals($id, $user->id);
        self::assertEquals($email, $user->email);
        self::assertEquals($role, $user->role);
        self::assertEquals($firstName, $user->firstName);
        self::assertEquals($lastName, $user->lastName);
        self::assertEmpty(array_diff($teamIds, array_column($user->teams, 'id')));
        self::assertEmpty(array_diff(array_column($user->teams, 'id'), $teamIds));
        self::assertObjectNotHasAttribute('password', $user);

        $user->password = $password;

        return $user;
    }

    /**
     * @depends testUserCanBeCreated
     * @param object $user
     */
    public function testChangingUserPasswordFailsIfOldPasswordWrong(object $user): void
    {
        $email = $user->email;
        $id = $user->id;
        $oldPassword = $user->password;
        $newPassword = DataGenerator::generatePassword();

        $userAuth = self::$client->authenticate(new BasicAuth($email, $oldPassword));

        $this->expectClientException();

        self::$client->request(new UpdateUserPassword([
            'id' => $id,
            'oldPassword' => $oldPassword . '_invalid',
            'newPassword' => $newPassword
        ]), $userAuth);
    }

    /**
     * @depends testUserCanBeCreated
     * @param object $user
     * @return string
     */
    public function testUserCanChangePassword(object $user): string
    {
        $email = $user->email;
        $id = $user->id;
        $oldPassword = $user->password;
        $newPassword = DataGenerator::generatePassword();

        $userAuth = self::$client->authenticate(new BasicAuth($email, $oldPassword));

        self::$client->request(new UpdateUserPassword([
            'id' => $id,
            'oldPassword' => $oldPassword,
            'newPassword' => $newPassword
        ]), $userAuth);

        sleep(1); // Workaround for issue "Password has changed after token has been issued"
        $userAuth = self::$client->authenticate(new BasicAuth($email, $newPassword));
        $user = $this->getUser($id, $userAuth);
        self::assertIsObject($user);
        self::assertEquals($id, $user->id);
        self::assertEquals($email, $user->email);

        return $id;
    }

    /**
     * @depends testUserCanChangePassword
     * @param string $id
     * @return string
     */
    public function testUserCanBeUpdated(string $id): string
    {
        $email = DataGenerator::generateEmail();
        $role = 'admin';
        $firstName = DataGenerator::generateString(8);
        $lastName = DataGenerator::generateString(8);
        $teamIds = [$this->createTeam()];

        self::$client->request(new UpdateUser([
            'id' => $id,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'role' => $role,
            'teamIds' => $teamIds
        ]), $this->defaultAdminAuth);

        $user = $this->getUser($id, $this->defaultAdminAuth);
        self::assertIsObject($user);
        self::assertEquals($id, $user->id);
        self::assertEquals($email, $user->email);
        self::assertEquals($role, $user->role);
        self::assertEquals($firstName, $user->firstName);
        self::assertEquals($lastName, $user->lastName);
        self::assertEmpty(array_diff($teamIds, array_column($user->teams, 'id')));
        self::assertEmpty(array_diff(array_column($user->teams, 'id'), $teamIds));

        return $id;
    }

    /**
     * @depends testUserCanBeUpdated
     * @param string $userId
     * @return string
     */
    public function testPasswordResetSendsAnEmail(string $userId): string
    {
        $user = $this->getUser($userId, $this->defaultAdminAuth);
        self::assertIsObject($user);

        self::$mailClient->deleteMails();
        self::$client->request(new SendPasswordResetMail([
            'email' => $user->email,
            'targetPath' => '/straight/to/hell'
        ]));

        $mails = self::waitForMailsToArrive();

        self::assertCount(1, $mails);
        $mail = current($mails);
        self::assertIsObject($mail);
        $recipients = $mail->to;
        self::assertCount(1, $recipients);
        $recipient = current($recipients);
        self::assertIsObject($recipient);
        self::assertEquals($user->email, $recipient->address);

        return $userId;
    }

    public function testPasswordResetDoesNotErrorWithUnknownEmail(): void
    {
        self::$mailClient->deleteMails();
        self::$client->request(new SendPasswordResetMail([
            'email' => DataGenerator::generateEmail(),
            'targetPath' => '/nowhere'
        ]));

        $mails = self::waitForMailsToArrive();

        self::assertCount(0, $mails);
    }

    /**
     * @depends testPasswordResetSendsAnEmail
     * @param string $userId
     * @return string
     */
    public function testSendingInviteEmail(string $userId): string
    {
        $user = $this->getUser($userId, $this->defaultAdminAuth);
        self::assertIsObject($user);

        self::$mailClient->deleteMails();
        self::$client->request(new SendInviteMail([
            'userId' => $userId,
            'targetPath' => '/nowhere'
        ]), $this->defaultAdminAuth);

        $mails = self::waitForMailsToArrive();

        self::assertCount(1, $mails);
        $mail = current($mails);
        self::assertIsObject($mail);
        $recipients = $mail->to;
        self::assertCount(1, $recipients);
        $recipient = current($recipients);
        self::assertIsObject($recipient);
        self::assertEquals($user->email, $recipient->address);

        return $userId;
    }

    public function testAccessTokensCanBeInvalidatedByUser(): void
    {
        $id = DataGenerator::generateId();
        $email = DataGenerator::generateEmail();
        $password = DataGenerator::generatePassword();
        $role = 'team_manager';
        $firstName = DataGenerator::generateString(8);
        $lastName = DataGenerator::generateString(8);
        $teamIds = [];

        self::assertNull($this->getUser($id, $this->defaultAdminAuth));

        self::$client->request(new CreateUser([
            'id' => $id,
            'email' => $email,
            'password' => $password,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'role' => $role,
            'teamIds' => $teamIds
        ]), $this->defaultAdminAuth);

        sleep(1); // Workaround for issue "Password has changed after token has been issued"

        $bearerAuth = self::$client->authenticate(new BasicAuth($email, $password));

        self::$client->request(new InvalidateAccessTokens([
            'userId' => $id
        ]), $bearerAuth);

        $this->expectClientException();
        $this->getUser($id, $bearerAuth);
    }

    public function testAccessTokensCanBeInvalidatedByAdmin(): void
    {
        $id = DataGenerator::generateId();
        $email = DataGenerator::generateEmail();
        $password = DataGenerator::generatePassword();
        $role = 'team_manager';
        $firstName = DataGenerator::generateString(8);
        $lastName = DataGenerator::generateString(8);
        $teamIds = [];

        self::assertNull($this->getUser($id, $this->defaultAdminAuth));

        self::$client->request(new CreateUser([
            'id' => $id,
            'email' => $email,
            'password' => $password,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'role' => $role,
            'teamIds' => $teamIds
        ]), $this->defaultAdminAuth);

        sleep(1); // Workaround for issue "Password has changed after token has been issued"

        $bearerAuth = self::$client->authenticate(new BasicAuth($email, $password));

        self::$client->request(new InvalidateAccessTokens([
            'userId' => $id
        ]), $this->defaultAdminAuth);

        $this->expectClientException();
        $this->getUser($id, $bearerAuth);
    }

    /**
     * @depends testSendingInviteEmail
     * @param string $id
     */
    public function testUserCanBeDeleted(string $id): void
    {
        self::assertNotNull($this->getUser($id, $this->defaultAdminAuth));

        self::$client->request(new DeleteUser([
            'id' => $id
        ]), $this->defaultAdminAuth);

        self::assertNull($this->getUser($id, $this->defaultAdminAuth));
    }

    public function testListingUsersRequiresAdminPermissions(): void
    {
        $query = new UserList();
        $this->expectClientException();
        self::$client->request($query);
    }

    public function testUsersCanBeListed(): void
    {
        $userList = self::$client->request(new UserList(), $this->defaultAdminAuth);

        self::assertIsArray($userList);
        self::assertNotEmpty($userList);

        foreach ($userList as $user) {
            self::assertObjectHasAttribute('id', $user);
            self::assertObjectHasAttribute('email', $user);
            self::assertObjectHasAttribute('role', $user);
            self::assertObjectHasAttribute('firstName', $user);
            self::assertObjectHasAttribute('lastName', $user);

            self::assertIsArray($user->teams);
            foreach ($user->teams as $team) {
                self::assertObjectHasAttribute('id', $team);
                self::assertObjectHasAttribute('name', $team);
            }
        }
    }

    private function getUser(?string $id = null, ?Auth $auth = null): ?object
    {
        return self::$client->request(new User(['id' => $id]), $auth);
    }

    private function createTeam(): string
    {
        $id = DataGenerator::generateId();

        self::$client->request(new CreateTeam([
            'id' => $id,
            'name' => DataGenerator::generateString(8)
        ]), $this->defaultAdminAuth);

        return $id;
    }
}
