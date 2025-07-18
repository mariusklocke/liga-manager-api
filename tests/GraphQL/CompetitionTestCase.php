<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Tests\Framework\DataGenerator;

abstract class CompetitionTestCase extends TestCase
{
    public const STATE_PREPARATION = 'preparation';
    public const STATE_PROGRESS = 'progress';
    public const STATE_ENDED = 'ended';
    
    protected static array $teamIds = [];
    protected static array $pitchIds = [];
    protected static array $teamManagers = [];
    protected static array $spareTeamIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();

        if (empty(self::$teamIds)) {
            $this->createTeams();
        }

        if (empty(self::$spareTeamIds)) {
            $this->createSpareTeams();
        }

        if (empty(self::$pitchIds)) {
            $this->createPitches();
        }
    }

    private function createTeams(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $teamId = DataGenerator::generateId();
            $this->client->createTeam($teamId, $teamId);
            self::$teamIds[] = $teamId;

            $manager = $this->generateTeamManager($teamId, $i);
            $this->client->createUser($manager);
            self::$teamManagers[$teamId] = $manager;
        }
    }

    private function createSpareTeams(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $teamId = DataGenerator::generateId();
            $this->client->createTeam($teamId, $teamId);
            self::$spareTeamIds[] = $teamId;

            $manager = $this->generateTeamManager($teamId, $i);
            $this->client->createUser($manager);
            self::$teamManagers[$teamId] = $manager;
        }
    }

    private function createPitches(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $id = DataGenerator::generateId();
            $label = 'Pitch' . $i;
            $this->client->createPitch($id, $label, DataGenerator::generateLatitude(), DataGenerator::generateLongitude());
            self::$pitchIds[] = $id;
        }
    }

    protected static function createMatchDayDates(int $count): array
    {
        $result = [];
        $start  = new DateTime('2024-10-05');
        $end    = new DateTime('2024-10-06');
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

    protected static function createMatchAppointments(DateTimeZone $timeZone): array
    {
        $appointments = [];
        $saturday = new DateTimeImmutable('2024-10-05', $timeZone);
        $sunday = new DateTimeImmutable('2024-10-06', $timeZone);

        $appointments[] = [
            'kickoff' => $saturday->setTime(15, 30)->format(DATE_ATOM),
            'unavailable_team_ids' => [],
            'pitch_id' => self::$pitchIds[0]
        ];

        $appointments[] = [
            'kickoff' => $saturday->setTime(17, 30)->format(DATE_ATOM),
            'unavailable_team_ids' => [self::$teamIds[0], self::$teamIds[1]],
            'pitch_id' => self::$pitchIds[1]
        ];

        $appointments[] = [
            'kickoff' => $sunday->setTime(12, 00)->format(DATE_ATOM),
            'unavailable_team_ids' => [self::$teamIds[2]],
            'pitch_id' => self::$pitchIds[0]
        ];

        $appointments[] = [
            'kickoff' => $sunday->setTime(14, 00)->format(DATE_ATOM),
            'unavailable_team_ids' => [],
            'pitch_id' => self::$pitchIds[1]
        ];

        return $appointments;
    }

    protected function useTeamManagerAuth(string $teamId): void
    {
        $user = self::$teamManagers[$teamId];
        $this->client->useCredentials($user['email'], $user['password']);
    }

    private function generateTeamManager(string $teamId, int $i): array
    {
        return [
            'id' => DataGenerator::generateId(),
            'email' => DataGenerator::generateEmail(),
            'password' => DataGenerator::generatePassword(),
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'role' => User::ROLE_TEAM_MANAGER,
            'team_ids' => [$teamId]
        ];
    }
}
