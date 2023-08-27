<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreatePitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeletePitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdatePitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\Pitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\PitchList;

class PitchTest extends TestCase
{
    public function testPitchCanBeCreated(): string
    {
        $id = DataGenerator::generateId();
        $label = DataGenerator::generateString(8);
        $location = DataGenerator::generateGeoLocation();

        self::assertNull($this->getPitch($id));

        self::$client->request(new CreatePitch([
            'id' => $id,
            'label' => $label,
            'location' => $location
        ]), $this->defaultAdminAuth);

        $pitch = $this->getPitch($id);
        self::assertIsObject($pitch);
        self::assertEquals($id, $pitch->id);
        self::assertEquals($label, $pitch->label);
        self::assertSimilarFloats($location->latitude, $pitch->location->latitude);
        self::assertSimilarFloats($location->longitude, $pitch->location->longitude);

        return $id;
    }

    /**
     * @depends testPitchCanBeCreated
     * @param string $id
     * @return string
     */
    public function testPitchCanBeUpdated(string $id): string
    {
        $label = DataGenerator::generateString(8);
        $location = DataGenerator::generateGeoLocation();

        self::$client->request(new UpdatePitch([
            'id' => $id,
            'label' => $label,
            'location' => $location
        ]), $this->defaultAdminAuth);

        $pitch = $this->getPitch($id);
        self::assertIsObject($pitch);
        self::assertEquals($id, $pitch->id);
        self::assertEquals($label, $pitch->label);
        self::assertSimilarFloats($location->latitude, $pitch->location->latitude);
        self::assertSimilarFloats($location->longitude, $pitch->location->longitude);

        return $id;
    }

    /**
     * @depends testPitchCanBeUpdated
     * @param string $id
     */
    public function testPitchCanBeDeleted(string $id): void
    {
        self::assertNotNull($this->getPitch($id));

        self::$client->request(new DeletePitch([
            'id' => $id
        ]), $this->defaultAdminAuth);

        self::assertNull($this->getPitch($id));
    }

    public function testPitchesCanBeListed(): void
    {
        $pitchList = self::$client->request(new PitchList());

        self::assertIsArray($pitchList);
        self::assertNotEmpty($pitchList);

        foreach ($pitchList as $pitch) {
            self::assertObjectHasProperty('id', $pitch);
            self::assertObjectHasProperty('label', $pitch);

            if (isset($pitch->location)) {
                self::assertObjectHasProperty('latitude', $pitch->location);
                self::assertObjectHasProperty('longitude', $pitch->location);
            }

            if (isset($pitch->contact)) {
                self::assertObjectHasProperty('firstName', $pitch->contact);
                self::assertObjectHasProperty('lastName', $pitch->contact);
                self::assertObjectHasProperty('phone', $pitch->contact);
                self::assertObjectHasProperty('email', $pitch->contact);
            }

            self::assertObjectHasProperty('matches', $pitch);
            self::assertIsArray($pitch->matches);
            foreach ($pitch->matches as $match) {
                self::assertObjectHasProperty('id', $match);
            }
        }
    }

    private function getPitch(string $id): ?object
    {
        return self::$client->request(new Pitch(['id' => $id]));
    }
}
