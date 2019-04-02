<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Domain\User;

abstract class CompetitionTestCase extends TestCase
{
    protected static $teamIds = [];
    protected static $teamManagers = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();

        if (empty(self::$teamIds)) {
            for ($i = 1; $i <= 8; $i++) {
                $teamId = 'Team' . $i;
                $this->client->createTeam($teamId, $teamId);
                self::$teamIds[] = $teamId;

                $manager = [
                    'id' => 'TeamManager' . $i,
                    'email' => 'team' . $i . '@example.com',
                    'password' => '123456',
                    'first_name' => 'Foo',
                    'last_name' => 'Bar',
                    'role' => User::ROLE_TEAM_MANAGER,
                    'team_ids' => [$teamId]
                ];
                $this->client->createUser($manager);
                self::$teamManagers[$teamId] = $manager;
            }
        }
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