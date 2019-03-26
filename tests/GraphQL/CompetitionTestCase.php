<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Tests\Framework\Fixtures;

abstract class CompetitionTestCase extends TestCase
{
    protected static $teamIds = [];
    protected static $teamManagers = [];

    public static function setUpBeforeClass(): void
    {
        $client = self::createClient();
        $client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
        for ($i = 1; $i <= 8; $i++) {
            $teamId = 'Team' . $i;
            $client->createTeam($teamId, $teamId);
            self::$teamIds[] = $teamId;

            $manager = [
                'id' => 'TeamManager' . $i,
                'email' => 'team' . $i . '@example.com',
                'password' => '123456',
                'firstName' => 'Foo',
                'lastName' => 'Bar',
                'role' => User::ROLE_TEAM_MANAGER,
                'teamIds' => [$teamId]
            ];
            $client->createUser($manager);
            self::$teamManagers[$teamId] = $manager;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
    }

    protected static function createMatchDayDates(int $count): array
    {
        $result = [];
        $start  = new \DateTime('2019-03-21');
        $end    = new \DateTime('2019-03-22');
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'from' => $start->format('Y-m-d'),
                'to'   => $end->format('Y-m-d')
            ];
            $start->modify('+7 days');
            $end->modify('+7 days');
        }

        return $result;
    }

    protected function useTeamManagerAuth(string $teamId)
    {
        $user = self::$teamManagers[$teamId];
        $this->client->useCredentials($user['email'], $user['password']);
    }
}