<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateTeam;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteTeam;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateTeam;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\Team;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\TeamList;
use stdClass;

class TeamTest extends TestCase
{
    public function testTeamCanBeCreated(): string
    {
        $id = DataGenerator::generateId();
        $name = DataGenerator::generateString(8);
        $contact = new stdClass();
        $contact->firstName = DataGenerator::generateString(8);
        $contact->lastName = DataGenerator::generateString(8);
        $contact->phone = DataGenerator::generateString(8);
        $contact->email = DataGenerator::generateEmail();

        self::assertNull($this->getTeam($id));
        self::$client->request(new CreateTeam([
            'id' => $id,
            'name' => $name,
            'contact' => $contact
        ]), $this->defaultAdminAuth);
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
        $name = DataGenerator::generateString(8);
        $contact = new stdClass();
        $contact->firstName = DataGenerator::generateString(8);
        $contact->lastName = DataGenerator::generateString(8);
        $contact->phone = DataGenerator::generateString(8);
        $contact->email = DataGenerator::generateEmail();

        self::$client->request(new UpdateTeam([
            'id' => $id,
            'name' => $name,
            'contact' => $contact
        ]), $this->defaultAdminAuth);
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
        self::$client->request(new DeleteTeam([
            'id' => $id
        ]), $this->defaultAdminAuth);
        self::assertNull($this->getTeam($id));
    }

    public function testTeamsCanBeListed(): void
    {
        $teamList = self::$client->request(new TeamList(), $this->defaultAdminAuth);

        self::assertIsArray($teamList);
        self::assertNotEmpty($teamList);
        foreach ($teamList as $team) {
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
        $query = new TeamList();
        $this->expectClientException();
        self::$client->request($query);
    }

    private function getTeam(string $id): ?object
    {
        return self::$client->request(new Team(['id' => $id]));
    }
}