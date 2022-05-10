<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Auth;
use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateUser;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteUser;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateUser;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateUserPassword;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\User;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\UserList;
use HexagonalPlayground\Tests\Framework\IdGenerator;

class UserTest extends TestCase
{
    public function testUserCanBeCreated(): object
    {
        $id = IdGenerator::generate();
        $email = 'skyler.white@example.com';
        $password = self::generatePassword();
        $role = 'team_manager';
        $firstName = 'Skyler';
        $lastName = 'White';
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
     * @return string
     */
    public function testUserCanChangePassword(object $user): string
    {
        $email = $user->email;
        $id = $user->id;
        $oldPassword = $user->password;
        $newPassword = self::generatePassword();

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
        $email = 'jessie.pinkman@example.com';
        $role = 'team_manager';
        $firstName = 'Jessie';
        $lastName = 'Pinkman';
        $teamIds = [];

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

    private static function generatePassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
