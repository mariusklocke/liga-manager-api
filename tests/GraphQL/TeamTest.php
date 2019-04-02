<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

class TeamTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();
    }

    /**
     * @return string
     */
    public function testTeamCanBeCreated(): string
    {
        $sent = ['id' => 'TeamA', 'name' => 'Team A'];
        $this->client->createTeam($sent['id'], $sent['name']);

        $received = $this->client->getTeamById($sent['id']);
        self::assertNotNull($received);
        self::assertSame($sent['id'], $received->id);
        self::assertSame($sent['name'], $received->name);

        $allTeams = $this->client->getAllTeams();
        self::assertArrayContainsObjectWithAttribute($allTeams, 'id', $sent['id']);

        return $sent['id'];
    }

    /**
     * @depends testTeamCanBeCreated
     * @param string $teamId
     * @return string
     */
    public function testTeamCanBeRenamed(string $teamId): string
    {
        $team    = $this->client->getTeamById($teamId);
        $newName = 'foobar';
        self::assertNotSame($newName, $team->name);

        $this->client->renameTeam($teamId, $newName);
        $team = $this->client->getTeamById($teamId);

        self::assertSame($newName, $team->name);

        return $teamId;
    }

    /**
     * @depends testTeamCanBeRenamed
     * @param string $teamId
     * @return string
     */
    public function testTeamContactCanBeUpdated(string $teamId): string
    {
        $team = $this->client->getTeamById($teamId);
        self::assertNotNull($team);
        self::assertNull($team->contact);

        $contact = [
            'first_name' => 'Marty',
            'last_name'  => 'McFly',
            'phone'      => '0123456',
            'email'      => 'marty@example.com'
        ];
        $this->client->updateTeamContact($teamId, $contact);

        $team = $this->client->getTeamById($teamId);
        self::assertNotNull($team);
        self::assertSame($contact, (array)$team->contact);

        return $teamId;
    }

    /**
     * @depends testTeamContactCanBeUpdated
     * @param string $teamId
     */
    public function testTeamCanBeDeleted(string $teamId)
    {
        $team = $this->client->getTeamById($teamId);
        self::assertNotNull($team);

        $this->client->deleteTeam($teamId);

        $team = $this->client->getTeamById($teamId);
        self::assertNull($team);
    }
}