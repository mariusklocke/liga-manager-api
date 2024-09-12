<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

class SchemaTest extends TestCase
{
    public function testSchemaCanBeFetched(): void
    {
        $schema = $this->client->getSchema();

        self::assertStringContainsString('type query', $schema);
        self::assertStringContainsString('type mutation', $schema);
    }
}
