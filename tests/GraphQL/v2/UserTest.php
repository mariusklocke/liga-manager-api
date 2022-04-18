<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\BearerAuth;

class UserTest extends TestCase
{
    private BearerAuth $adminAuth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminAuth = $this->authenticate($this->defaultAdminAuth);
    }

    public function testListingUsersRequiresAdminPermissions(): void
    {
        $query = $this->createQuery('userList')
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

        $response = $this->request($query);

        self::assertObjectHasAttribute('errors', $response);
        self::assertObjectNotHasAttribute('data', $response);
    }

    public function testUsersCanBeListed(): void
    {
        $query = $this->createQuery('userList')
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

        $response = $this->request($query, $this->adminAuth);

        self::assertResponseNotHasError($response);
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
}
