<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Tests\Framework\IdGenerator;

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
        $sent = ['id' => IdGenerator::generate(), 'name' => 'Team A'];
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
     * @return string
     */
    public function testTeamLogoCanBeUploaded(string $teamId): string
    {
        $tempFile = $this->generateRandomFile();
        try {
            $token = $this->createAdminToken();
            $method = 'POST';
            $url = "/api/logos?teamId=$teamId";
            $fileMediaType = 'image/webp';
            $headers = ['Authorization' => "Bearer $token"];

            // Upload logo
            $response = $this->slimClient->sendUploadRequest($method, $url, $tempFile, $fileMediaType, $headers);
            self::assertSame(201, $response->getStatusCode());
            self::assertStringStartsWith('/logos', $response->getHeader('Location')[0]);

            // Verify logo is present
            $response = $this->slimClient->get($url, $headers);
            self::assertSame(302, $response->getStatusCode());
            self::assertStringStartsWith('/logos', $response->getHeader('Location')[0]);
        } finally {
            unlink($tempFile);
        }

        return $teamId;
    }

    /**
     * @depends testTeamLogoCanBeUploaded
     * @param string $teamId
     * @return string
     */
    public function testTeamLogoCanDeDeleted(string $teamId): string
    {
        $token = $this->createAdminToken();
        $url = "/api/logos?teamId=$teamId";
        $headers = ['Authorization' => "Bearer $token"];
        $response = $this->slimClient->delete($url, $headers);
        self::assertSame(204, $response->getStatusCode());

        $response = $this->slimClient->get($url, $headers);
        self::assertSame(404, $response->getStatusCode());

        return $teamId;
    }

    /**
     * @depends testTeamLogoCanDeDeleted
     * @param string $teamId
     */
    public function testTeamCanBeDeleted(string $teamId): void
    {
        $team = $this->client->getTeamById($teamId);
        self::assertNotNull($team);

        $this->client->deleteTeam($teamId);

        $team = $this->client->getTeamById($teamId);
        self::assertNull($team);
    }

    private function generateRandomFile(): string
    {
        $tempFilename = sprintf("random_image_%s.webp", uniqid());
        $tempPath = join(DIRECTORY_SEPARATOR, [sys_get_temp_dir(), $tempFilename]);

        file_put_contents($tempPath, bin2hex(random_bytes(16)));

        return $tempPath;
    }
}
