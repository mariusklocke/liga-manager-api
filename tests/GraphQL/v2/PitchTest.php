<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

class PitchTest extends TestCase
{
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
}
