<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\BearerAuth;

class TeamTest extends TestCase
{
    private BearerAuth $adminAuth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminAuth = $this->authenticate($this->defaultAdminAuth);
    }

    public function testTeamsCanBeListed(): void
    {
        $query = $this->createQuery('teamList')
            ->fields([
                'id',
                'name',
                'createdAt',
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ],
                'users' => [
                    'id',
                    'email'
                ],
                'homeMatches' => [
                    'id'
                ],
                'guestMatches' => [
                    'id'
                ]
            ]);

        $response = $this->request($query, $this->adminAuth);

        self::assertResponseNotHasError($response);
        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('teamList', $response->data);
        self::assertIsArray($response->data->teamList);
        self::assertNotEmpty($response->data->teamList);

        foreach ($response->data->teamList as $team) {
            self::assertObjectHasAttribute('id', $team);
            self::assertObjectHasAttribute('name', $team);
            self::assertObjectHasAttribute('createdAt', $team);

            if (isset($team->contact)) {
                self::assertObjectHasAttribute('firstName', $team->contact);
                self::assertObjectHasAttribute('lastName', $team->contact);
                self::assertObjectHasAttribute('phone', $team->contact);
                self::assertObjectHasAttribute('email', $team->contact);
            }

            self::assertIsArray($team->users);
            foreach ($team->users as $user) {
                self::assertObjectHasAttribute('id', $user);
                self::assertObjectHasAttribute('email', $user);
            }

            self::assertIsArray($team->homeMatches);
            foreach ($team->homeMatches as $match) {
                self::assertObjectHasAttribute('id', $match);
            }

            self::assertIsArray($team->guestMatches);
            foreach ($team->guestMatches as $match) {
                self::assertObjectHasAttribute('id', $match);
            }
        }
    }

    public function testListingAssociatedUsersRequiresAdminPermission(): void
    {
        $query = $this->createQuery('teamList')
            ->fields([
                'id',
                'name',
                'users' => [
                    'id',
                    'email'
                ]
            ]);

        $response = $this->request($query);

        self::assertObjectHasAttribute('errors', $response);
        self::assertObjectNotHasAttribute('data', $response);
    }
}
