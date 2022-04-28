<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreatePitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateTeam;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateUser;
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
            $id = IdGenerator::generate();
            self::$client->request(new CreateTeam([
                'id' => $id,
                'name' => $id
            ]), $this->defaultAdminAuth);
            yield $id;
        }
    }

    private function generateTeamsManagers(array $teamIds): Iterator
    {
        foreach ($teamIds as $teamId) {
            $userId = IdGenerator::generate();
            $email = $userId . '@example.com';
            $password = self::generatePassword();

            self::$client->request(new CreateUser([
                'id' => $userId,
                'email' => $email,
                'password' => $password,
                'firstName' => $userId,
                'lastName' => $userId,
                'role' => 'team_manager',
                'teamIds' => [$teamId]
            ]), $this->defaultAdminAuth);

            yield new BasicAuth($email, $password);
        }
    }

    private function generatePitches(int $count): Iterator
    {
        for ($i = 1; $i <= $count; $i++) {
            $id = IdGenerator::generate();
            self::$client->request(new CreatePitch([
                'id' => $id,
                'label' => $id
            ]), $this->defaultAdminAuth);
            yield $id;
        }
    }

    private static function generatePassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
