<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\File;
use PHPUnit\Framework\Attributes\Depends;

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
        $sent = ['id' => DataGenerator::generateId(), 'name' => 'Team A'];
        $this->client->createTeam($sent['id'], $sent['name']);

        $received = $this->client->getTeamById($sent['id']);
        self::assertNotNull($received);
        self::assertSame($sent['id'], $received->id);
        self::assertSame($sent['name'], $received->name);

        $allTeams = $this->client->getAllTeams();
        self::assertArrayContainsObjectWithAttribute($allTeams, 'id', $sent['id']);

        $teams = $this->client->getTeamsByPattern('Team*');
        self::assertArrayContainsObjectWithAttribute($teams, 'id', $sent['id']);

        return $sent['id'];
    }

    /**
     * @param string $teamId
     * @return string
     */
    #[Depends("testTeamCanBeCreated")]
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
     * @param string $teamId
     * @return string
     */
    #[Depends("testTeamCanBeRenamed")]
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
     * @param string $teamId
     * @return string
     */
    #[Depends("testTeamContactCanBeUpdated")]
    public function testTeamLogoCanBeUploaded(string $teamId): string
    {
        $tempFile = $this->generateRandomFile();
        try {
            $token = $this->createAdminToken();
            $url = "/api/logos?teamId=$teamId";
            $fileMediaType = 'image/webp';
            $headers = ['Authorization' => "Bearer $token"];

            // Upload logo
            $request = $this->buildUploadRequest('POST', $url, $tempFile->getPath(), $fileMediaType, $headers);
            $response = $this->psrClient->sendRequest($request);
            self::assertSame(201, $response->getStatusCode(), (string)$response->getBody());
            self::assertStringStartsWith('/logos', $response->getHeader('Location')[0]);

            // Verify logo is present
            $request = $this->buildRequest('GET', $url, $headers);
            $response = $this->psrClient->sendRequest($request);
            self::assertSame(302, $response->getStatusCode(), (string)$response->getBody());
            self::assertStringStartsWith('/logos', $response->getHeader('Location')[0]);
        } finally {
            $tempFile->delete();
        }

        return $teamId;
    }

    /**
     * @param string $teamId
     * @return string
     */
    #[Depends("testTeamLogoCanBeUploaded")]
    public function testTeamLogoCanDeDeleted(string $teamId): string
    {
        $token = $this->createAdminToken();
        $url = "/api/logos?teamId=$teamId";
        $headers = ['Authorization' => "Bearer $token"];
        $request = $this->buildRequest('DELETE', $url, $headers);
        $response = $this->psrClient->sendRequest($request);
        self::assertSame(204, $response->getStatusCode());

        $request = $this->buildRequest('GET', $url, $headers);
        $response = $this->psrClient->sendRequest($request);
        self::assertSame(404, $response->getStatusCode());

        return $teamId;
    }

    /**
     * @param string $teamId
     */
    #[Depends("testTeamLogoCanDeDeleted")]
    public function testTeamCanBeDeleted(string $teamId): void
    {
        $team = $this->client->getTeamById($teamId);
        self::assertNotNull($team);

        $this->client->deleteTeam($teamId);

        $team = $this->client->getTeamById($teamId);
        self::assertNull($team);
    }

    private function generateRandomFile(): File
    {
        $tempFile = File::temp('random_image_', '.webp');
        $tempFile->write(bin2hex(random_bytes(16)));

        return $tempFile;
    }
}
