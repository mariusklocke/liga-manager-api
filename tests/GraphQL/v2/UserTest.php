<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Auth;
use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
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
        $this->createUser($id, $email, $password, $firstName, $lastName, $role, $teamIds);
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
        $this->updateUserPassword($id, $oldPassword, $newPassword, $userAuth);
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

        $this->updateUser($id, $email, $firstName, $lastName, $role, $teamIds);
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
        $this->deleteUser($id);
        self::assertNull($this->getUser($id, $this->defaultAdminAuth));
    }

    public function testListingUsersRequiresAdminPermissions(): void
    {
        $query = self::$client->createQuery('userList')
            ->fields([
                'id',
                'email',
                'role',
                'firstName',
                'lastName',
                'teams' => [
                    'id',
                    'name'
                ]
            ]);

        $this->expectClientException();
        self::$client->request($query);
    }

    public function testUsersCanBeListed(): void
    {
        $query = self::$client->createQuery('userList')
            ->fields([
                'id',
                'email',
                'role',
                'firstName',
                'lastName',
                'teams' => [
                    'id',
                    'name'
                ]
            ]);

        $response = self::$client->request($query, $this->defaultAdminAuth);

        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('userList', $response->data);
        self::assertIsArray($response->data->userList);
        self::assertNotEmpty($response->data->userList);

        foreach ($response->data->userList as $user) {
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

    private function createUser(
        string $id,
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        string $role,
        array $teamIds
    ): void {
        $mutation = self::$client->createMutation('createUser')
            ->argTypes([
                'id' => 'String!',
                'email' => 'String!',
                'password' => 'String!',
                'firstName' => 'String!',
                'lastName' => 'String!',
                'role' => 'String!',
                'teamIds' => '[String]!'
            ])
            ->argValues([
                'id' => $id,
                'email' => $email,
                'password' => $password,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'role' => $role,
                'teamIds' => $teamIds
            ]);

        self::$client->request($mutation, $this->defaultAdminAuth);
    }

    private function updateUser(
        string $id,
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        array $teamIds
    ): void {
        $mutation = self::$client->createMutation('updateUser')
            ->argTypes([
                'id' => 'String!',
                'email' => 'String!',
                'firstName' => 'String!',
                'lastName' => 'String!',
                'role' => 'String!',
                'teamIds' => '[String]!'
            ])
            ->argValues([
                'id' => $id,
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'role' => $role,
                'teamIds' => $teamIds
            ]);

        self::$client->request($mutation, $this->defaultAdminAuth);
    }

    private function getUser(?string $id = null, ?Auth $auth = null): ?object
    {
        $query = self::$client->createQuery('user')
            ->fields([
                'id',
                'email',
                'firstName',
                'lastName',
                'role',
                'teams' => [
                    'id',
                    'name'
                ]
            ])
            ->argTypes(['id' => 'String'])
            ->argValues(['id' => $id]);

        $response = self::$client->request($query, $auth);

        if (isset($response->data) && isset($response->data->user)) {
            return $response->data->user;
        }

        return null;
    }

    private function deleteUser(string $id): void
    {
        $mutation = self::$client->createMutation('deleteUser')
            ->argTypes([
                'id' => 'String!'
            ])
            ->argValues([
                'id' => $id
            ]);

        $response = self::$client->request($mutation, $this->defaultAdminAuth);
    }

    private function updateUserPassword(string $id, string $oldPassword, string $newPassword, ?Auth $auth = null): void
    {
        $mutation = self::$client->createMutation('updateUserPassword')
            ->argTypes([
                'id' => 'String!',
                'oldPassword' => 'String!',
                'newPassword' => 'String!'
            ])
            ->argValues([
                'id' => $id,
                'oldPassword' => $oldPassword,
                'newPassword' => $newPassword
            ]);

        self::$client->request($mutation, $auth);
    }

    private static function generatePassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
