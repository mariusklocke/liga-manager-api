<?php
declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205204400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change data type for public_key_credentials table from PHP-serialized objects to JSON';
    }

    public function up(Schema $schema): void
    {
        $this->transformCredentials(function (string $value): string {
            return json_encode(unserialize($value));
        });
    }

    public function down(Schema $schema): void
    {
        $this->transformCredentials(function (string $value): string {
            return serialize(json_decode($value));
        });
    }

    private function transformCredentials(callable $transformer): void
    {
        $query = 'SELECT `id`,`trust_path`,`aaguid` FROM `public_key_credentials`';

        foreach ($this->connection->iterateAssociative($query) as $credential) {
            $this->addSql(
                'UPDATE `public_key_credentials` SET trust_path = :trustPath, aaguid = :aaguid WHERE id = :id',
                [
                    'trust_path' => $transformer($credential['trust_path']),
                    'aaguid' => $transformer($credential['aaguid']),
                    'id' => $credential['id']
                ],
                [
                    'trust_path' => ParameterType::STRING,
                    'aaguid' => ParameterType::STRING,
                    'id' => ParameterType::STRING
                ]
            );
        }
    }
}
