<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v1;

use HexagonalPlayground\Tests\Framework\IdGenerator;

class PitchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();
    }

    public function testPitchCanBeCreated(): array
    {
        $floatPitchId = IdGenerator::generate();
        $intPitchId = IdGenerator::generate();
        $this->client->createPitch($floatPitchId, 'TestFloat', 89.99, 6.78);
        $this->client->createPitch($intPitchId, 'TestInt', 89, 6);

        $pitch = $this->client->getPitchById($floatPitchId);
        self::assertNotNull($pitch);
        self::assertSame($floatPitchId, $pitch->id);
        self::assertSame('TestFloat', $pitch->label);
        self::assertSimilarFloats(89.99, $pitch->location_latitude);
        self::assertSimilarFloats(6.78, $pitch->location_longitude);

        $pitch = $this->client->getPitchById($intPitchId);
        self::assertNotNull($pitch);
        self::assertSame($intPitchId, $pitch->id);
        self::assertSame('TestInt', $pitch->label);
        self::assertSame(89, $pitch->location_latitude);
        self::assertSame(6, $pitch->location_longitude);

        return [$floatPitchId, $intPitchId];
    }

    /**
     * @depends testPitchCanBeCreated
     * @param array $pitchIds
     * @return array
     */
    public function testPitchContactCanBeUpdated(array $pitchIds): array
    {
        $pitchId = $pitchIds[0];
        $contact = [
            'first_name' => 'Marty',
            'last_name'  => 'McFly',
            'phone'      => '0123456',
            'email'      => 'marty@example.com'
        ];

        $this->client->updatePitchContact($pitchId, $contact);
        $pitch = $this->client->getPitchById($pitchId);

        self::assertIsObject($pitch);
        self::assertEquals($contact, (array)$pitch->contact);

        return $pitchIds;
    }

    /**
     * @depends testPitchContactCanBeUpdated
     * @param array $pitchIds
     */
    public function testPitchCanBeDeleted(array $pitchIds)
    {
        foreach ($pitchIds as $pitchId) {
            $pitch = $this->client->getPitchById($pitchId);
            self::assertNotNull($pitch);

            $this->client->deletePitch($pitchId);
            $pitch = $this->client->getPitchById($pitchId);
            self::assertNull($pitch);
        }
    }
}
