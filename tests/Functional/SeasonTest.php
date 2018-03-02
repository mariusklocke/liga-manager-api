<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

class SeasonTest extends TestCase
{
    public function testSeasonCanBeCreated() : string
    {
        $client = static::getClient();
        $response = $client->post('/season', ['name' => 'bar']);
        self::assertEquals(200, $response->getStatusCode());
        $data = $client->parseBody($response->getBody());
        self::assertArrayHasKey('id', $data);
        self::assertGreaterThan(0, strlen($data['id']));

        return $data['id'];
    }

    /**
     * @param string $seasonId
     *
     * @depends testSeasonCanBeCreated
     */
    public function testSeasonCanBeFound(string $seasonId)
    {
        $client = static::getClient();
        $response = $client->get('/season/' . $seasonId);
        $season = $client->parseBody($response->getBody());
        self::assertEquals(200, $response->getStatusCode());
        self::assertArrayHasKey('id', $season);
        self::assertArrayHasKey('name', $season);
        self::assertArrayHasKey('state', $season);
    }
}