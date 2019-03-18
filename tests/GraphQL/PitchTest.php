<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Tests\Framework\Fixtures;

class PitchTest extends TestCase
{
    public function testPitchCanBeCreated(): array
    {
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
        $this->client->createPitch('TestFloat', 'TestFloat', 89.99, 6.78);
        $this->client->createPitch('TestInt', 'TestInt', 89, 6);

        $pitch = $this->client->getPitchById('TestFloat');
        self::assertSame('TestFloat', $pitch->id);
        self::assertSame('TestFloat', $pitch->label);
        self::assertSimilarFloats(89.99, $pitch->location_latitude);
        self::assertSimilarFloats(6.78, $pitch->location_longitude);

        $pitch = $this->client->getPitchById('TestInt');
        self::assertSame('TestInt', $pitch->id);
        self::assertSame('TestInt', $pitch->label);
        self::assertSame(89, $pitch->location_latitude);
        self::assertSame(6, $pitch->location_longitude);

        return ['TestFloat', 'TestInt'];
    }

    /**
     * @depends testPitchCanBeCreated
     * @param array $pitchIds
     * @return array
     */
    public function testPitchContactCanBeUpdated(array $pitchIds)
    {
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
        $pitchId = $pitchIds[0];
        $contact = [
            'first_name' => 'Marty',
            'last_name'  => 'McFly',
            'phone'      => '0123456',
            'email'      => 'marty@example.com'
        ];

        $this->client->updatePitchContact($pitchId, $contact);
        $pitch = $this->client->getPitchById($pitchId);

        self::assertObjectHasAttribute('contact', $pitch);
        self::assertEquals($contact, (array)$pitch->contact);

        return $pitchIds;
    }

    /**
     * @depends testPitchContactCanBeUpdated
     * @param array $pitchIds
     */
    public function testPitchCanBeDeleted(array $pitchIds)
    {
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
        foreach ($pitchIds as $pitchId) {
            $pitch = $this->client->getPitchById($pitchId);
            self::assertNotNull($pitch);

            $this->client->deletePitch($pitchId);
            $pitch = $this->client->getPitchById($pitchId);
            self::assertNull($pitch);
        }
    }
}