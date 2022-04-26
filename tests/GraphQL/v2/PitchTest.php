<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\BearerAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation;
use HexagonalPlayground\Tests\Framework\GraphQL\Query;
use HexagonalPlayground\Tests\Framework\IdGenerator;

class PitchTest extends TestCase
{
    private BearerAuth $adminAuth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminAuth = $this->authenticate($this->defaultAdminAuth);
    }

    public function testPitchCanBeCreated(): string
    {
        $id = IdGenerator::generate();
        $label = __METHOD__;
        $location = new \stdClass();
        $location->latitude = 89.99;
        $location->longitude = 6.78;

        self::assertNull($this->getPitch($id));
        $this->createPitch($id, $label, $location);
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
        $label = __METHOD__;
        $location = new \stdClass();
        $location->latitude = 12.34;
        $location->longitude = 5.99;

        $this->updatePitch($id, $label, $location);
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
        $this->deletePitch($id);
        self::assertNull($this->getPitch($id));
    }

    public function testPitchesCanBeListed(): void
    {
        $query = $this->createQuery('pitchList')
            ->fields([
                'id',
                'label',
                'location' => [
                    'latitude',
                    'longitude'
                ],
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ],
                'matches' => [
                    'id'
                ]
            ]);

        $response = $this->request($query);

        self::assertResponseNotHasError($response);
        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('pitchList', $response->data);
        self::assertIsArray($response->data->pitchList);
        self::assertNotEmpty($response->data->pitchList);

        foreach ($response->data->pitchList as $pitch) {
            self::assertObjectHasAttribute('id', $pitch);
            self::assertObjectHasAttribute('label', $pitch);

            if (isset($pitch->location)) {
                self::assertObjectHasAttribute('latitude', $pitch->location);
                self::assertObjectHasAttribute('longitude', $pitch->location);
            }

            if (isset($pitch->contact)) {
                self::assertObjectHasAttribute('firstName', $pitch->contact);
                self::assertObjectHasAttribute('lastName', $pitch->contact);
                self::assertObjectHasAttribute('phone', $pitch->contact);
                self::assertObjectHasAttribute('email', $pitch->contact);
            }

            self::assertObjectHasAttribute('matches', $pitch);
            self::assertIsArray($pitch->matches);
            foreach ($pitch->matches as $match) {
                self::assertObjectHasAttribute('id', $match);
            }
        }
    }

    private function createPitch(string $id, string $label, ?object $location): void
    {
        $mutation = (new Mutation('createPitch'))
            ->argTypes([
                'id' => 'String!',
                'label' => 'String!',
                'location' => 'GeoLocationInput'
            ])
            ->argValues([
                'id' => $id,
                'label' => $label,
                'location' => $location
            ]);

        $response = $this->request($mutation, $this->adminAuth);

        self::assertResponseNotHasError($response);
    }

    private function updatePitch(string $id, string $label, ?object $location): void
    {
        $mutation = (new Mutation('updatePitch'))
            ->argTypes([
                'id' => 'String!',
                'label' => 'String!',
                'location' => 'GeoLocationInput'
            ])
            ->argValues([
                'id' => $id,
                'label' => $label,
                'location' => $location
            ]);

        $response = $this->request($mutation, $this->adminAuth);

        self::assertResponseNotHasError($response);
    }

    private function deletePitch(string $id): void
    {
        $mutation = (new Mutation('deletePitch'))
            ->argTypes(['id' => 'String!'])
            ->argValues(['id' => $id]);

        $response = $this->request($mutation, $this->adminAuth);

        self::assertResponseNotHasError($response);
    }

    private function getPitch(string $id): ?object
    {
        $query = (new Query('pitch'))
            ->fields([
                'id',
                'label',
                'location' => [
                    'latitude',
                    'longitude'
                ],
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ]
            ])
            ->argTypes(['id' => 'String!'])
            ->argValues(['id' => $id]);

        $response = $this->request($query);

        if (isset($response->data) && isset($response->data->pitch)) {
            return $response->data->pitch;
        }

        return null;
    }
}
