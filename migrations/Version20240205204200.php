<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205204200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change data type for events table from PHP-serialized array to JSON';
    }

    public function up(Schema $schema): void
    {
        $this->transformEvents(function (string $payload): string {
            return json_encode(unserialize($payload));
        });
    }

    public function down(Schema $schema): void
    {
        $this->transformEvents(function (string $payload): string {
            return serialize(json_decode($payload, true));
        });
    }

    private function transformEvents(callable $transformer): void
    {
        $query = 'SELECT `id`, `payload` FROM `events`';

        foreach ($this->connection->iterateKeyValue($query) as $id => $payload) {
            $this->addSql(
                'UPDATE `events` SET payload = :payload WHERE id = :id',
                [
                    'payload' => $transformer($payload),
                    'id' => $id
                ],
                [
                    'payload' => ParameterType::STRING,
                    'id' => ParameterType::STRING
                ]
            );
        }
    }
}
