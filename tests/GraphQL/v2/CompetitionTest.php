<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use HexagonalPlayground\Tests\Framework\IdGenerator;
use Iterator;

abstract class CompetitionTest extends TestCase
{
    protected static array $teamIds = [];
    protected static array $spareTeamIds = [];
    protected static array $pitchIds = [];
    protected static array $teamManagerAuths = [];
    protected static array $spareTeamManagerAuths = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (empty(self::$teamIds)) {
            self::$teamIds = iterator_to_array($this->generateTeams(8));
        }

        if (empty(self::$spareTeamIds)) {
            self::$spareTeamIds = iterator_to_array($this->generateTeams(2));
        }

        if (empty(self::$teamManagerAuths)) {
            self::$teamManagerAuths = iterator_to_array($this->generateTeamsManagers(self::$teamIds));
        }

        if (empty(self::$spareTeamManagerAuths)) {
            self::$spareTeamManagerAuths = iterator_to_array($this->generateTeamsManagers(self::$spareTeamIds));
        }

        if (empty(self::$pitchIds)) {
            self::$pitchIds = iterator_to_array($this->generatePitches(2));
        }
    }

    private function generateTeams(int $count): Iterator
    {
        for ($i = 1; $i <= $count; $i++) {
            $teamId = IdGenerator::generate();
            $this->createTeam($teamId, $teamId, null);

            yield $teamId;
        }
    }

    private function generateTeamsManagers(array $teamIds): Iterator
    {
        foreach ($teamIds as $teamId) {
            $userId = IdGenerator::generate();
            $email = $userId . '@example.com';
            $password = self::generatePassword();

            $this->createUser(
                $userId,
                $email,
                $password,
                $userId,
                $userId,
                'team_manager',
                [$teamId]
            );

            yield new BasicAuth($email, $password);
        }
    }

    private function generatePitches(int $count): Iterator
    {
        for ($i = 1; $i <= $count; $i++) {
            $pitchId = IdGenerator::generate();
            $this->createPitch($pitchId, $pitchId, null);
            yield $pitchId;
        }
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

        self::$client->request($mutation, $this->defaultAdminAuth);
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

    private function createPitch(string $id, string $label, ?object $location): void
    {
        $mutation = self::$client->createMutation('createPitch')
            ->argTypes([
                'id' => 'String!',
                'label' => 'String!',
                'location' => 'GeoLocationInput'
            ])
            ->argValues([
                'id' => $id,
                'label' => $label,
                'location' => $location
            ]);

        self::$client->request($mutation, $this->defaultAdminAuth);
    }

    private static function generatePassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
