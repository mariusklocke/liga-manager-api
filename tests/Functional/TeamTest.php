<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

class TeamTest extends TestCase
{
    public function testTeamCanBeCreated() : string
    {
        $client = static::getClient();
        $response = $client->post('/team', []);
        self::assertEquals(400, $response->getStatusCode());
        $response = $client->post('/team', ['name' => 'foo']);
        self::assertEquals(200, $response->getStatusCode());
        $data = $client->parseBody($response->getBody());
        self::assertArrayHasKey('id', $data);

        return $data['id'];
    }

    /**
     * @param string $teamId
     * @depends testTeamCanBeCreated
     */
    public function testTeamCanBeDeleted(string $teamId)
    {
        $client = static::getClient();
        $response = $client->delete('/team/' . $teamId);
        self::assertEquals(204, $response->getStatusCode());
    }
}