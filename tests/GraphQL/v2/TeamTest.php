<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\IdGenerator;

class TeamTest extends TestCase
{
    public function testTeamCanBeCreated(): string
    {
        $id = IdGenerator::generate();
        $name = __METHOD__;
        $contact = new \stdClass();
        $contact->firstName = 'Marty';
        $contact->lastName = 'McFly';
        $contact->phone = '0123456';
        $contact->email = 'marty@example.com';

        self::assertNull($this->getTeam($id));
        $this->createTeam($id, $name, $contact);
        $team = $this->getTeam($id);
        self::assertIsObject($team);
        self::assertEquals($id, $team->id);
        self::assertEquals($name, $team->name);
        self::assertEquals($contact->firstName, $team->contact->firstName);
        self::assertEquals($contact->lastName, $team->contact->lastName);
        self::assertEquals($contact->phone, $team->contact->phone);
        self::assertEquals($contact->email, $team->contact->email);

        return $id;
    }

    /**
     * @depends testTeamCanBeCreated
     * @param string $id
     * @return string
     */
    public function testTeamCanBeUpdated(string $id): string
    {
        $name = __METHOD__;
        $contact = new \stdClass();
        $contact->firstName = 'Walter';
        $contact->lastName = 'White';
        $contact->phone = '911';
        $contact->email = 'walter.white@example.com';

        $this->updateTeam($id, $name, $contact);
        $team = $this->getTeam($id);
        self::assertIsObject($team);
        self::assertEquals($id, $team->id);
        self::assertEquals($name, $team->name);
        self::assertEquals($contact->firstName, $team->contact->firstName);
        self::assertEquals($contact->lastName, $team->contact->lastName);
        self::assertEquals($contact->phone, $team->contact->phone);
        self::assertEquals($contact->email, $team->contact->email);

        return $id;
    }

    /**
     * @depends testTeamCanBeUpdated
     * @param string $id
     */
    public function testTeamCanBeDeleted(string $id): void
    {
        self::assertNotNull($this->getTeam($id));
        $this->deleteTeam($id);
        self::assertNull($this->getTeam($id));
    }

    public function testTeamsCanBeListed(): void
    {
        $query = self::$client->createQuery('teamList')
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

        $response = self::$client->request($query, $this->defaultAdminAuth);

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
        $query = self::$client->createQuery('teamList')
            ->fields([
                'id',
                'name',
                'users' => [
                    'id',
                    'email'
                ]
            ]);

        $response = self::$client->request($query);

        self::assertObjectHasAttribute('errors', $response);
        self::assertObjectNotHasAttribute('data', $response);
    }

    private function createTeam(string $id, string $name, ?object $contact): void
    {
        $mutation = self::$client->createMutation('createTeam')
            ->argTypes([
                'id' => 'String!',
                'name' => 'String!',
                'contact' => 'ContactInput'
            ])
            ->argValues([
                'id' => $id,
                'name' => $name,
                'contact' => $contact
            ]);

        $response = self::$client->request($mutation, $this->defaultAdminAuth);

        self::assertResponseNotHasError($response);
    }

    private function updateTeam(string $id, string $name, ?object $contact): void
    {
        $mutation = self::$client->createMutation('updateTeam')
            ->argTypes([
                'id' => 'String!',
                'name' => 'String!',
                'contact' => 'ContactInput'
            ])
            ->argValues([
                'id' => $id,
                'name' => $name,
                'contact' => $contact
            ]);

        $response = self::$client->request($mutation, $this->defaultAdminAuth);

        self::assertResponseNotHasError($response);
    }

    private function deleteTeam(string $id): void
    {
        $mutation = self::$client->createMutation('deleteTeam')
            ->argTypes([
                'id' => 'String!',
            ])
            ->argValues([
                'id' => $id
            ]);

        $response = self::$client->request($mutation, $this->defaultAdminAuth);

        self::assertResponseNotHasError($response);
    }

    private function getTeam(string $id): ?object
    {
        $query = self::$client->createQuery('team')
            ->fields([
                'id',
                'name',
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ]
            ])
            ->argTypes(['id' => 'String!'])
            ->argValues(['id' => $id]);

        $response = self::$client->request($query);

        if (isset($response->data) && isset($response->data->team)) {
            return $response->data->team;
        }

        return null;
    }
}
