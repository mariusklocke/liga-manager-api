<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use PHPUnit\Framework\Attributes\Depends;

class PitchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();
    }

    public function testPitchCanBeCreated(): array
    {
        $inputData = [
            [
                'id' => DataGenerator::generateId(),
                'label' => 'TestFloat',
                'latitude' => DataGenerator::generateLatitude(),
                'longitude' => DataGenerator::generateLongitude(),
            ],
            [
                'id' => DataGenerator::generateId(),
                'label' => 'TestInt',
                'latitude' => (int)DataGenerator::generateLatitude(),
                'longitude' => (int)DataGenerator::generateLongitude(),
            ]
        ];

        foreach ($inputData as $pitch) {
            $this->client->createPitch(
                $pitch['id'],
                $pitch['label'],
                $pitch['latitude'],
                $pitch['longitude']
            );
        }

        $pitch = $this->client->getPitchById($inputData[0]['id']);
        self::assertNotNull($pitch);
        self::assertSame($inputData[0]['id'], $pitch->id);
        self::assertSame($inputData[0]['label'], $pitch->label);
        self::assertSimilarFloats($inputData[0]['latitude'], $pitch->location_latitude);
        self::assertSimilarFloats($inputData[0]['longitude'], $pitch->location_longitude);

        $pitch = $this->client->getPitchById($inputData[1]['id']);
        self::assertNotNull($pitch);
        self::assertSame($inputData[1]['id'], $pitch->id);
        self::assertSame($inputData[1]['label'], $pitch->label);
        self::assertSame($inputData[1]['latitude'], $pitch->location_latitude);
        self::assertSame($inputData[1]['longitude'], $pitch->location_longitude);

        return [$inputData[0]['id'], $inputData[1]['id']];
    }

    /**
     * @param array $pitchIds
     * @return array
     */
    #[Depends("testPitchCanBeCreated")]
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
     * @param array $pitchIds
     */
    #[Depends("testPitchContactCanBeUpdated")]
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
