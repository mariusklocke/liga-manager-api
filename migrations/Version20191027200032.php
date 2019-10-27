<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191027200032 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds table for public key credentials';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE public_key_credentials (id VARBINARY(64) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, transports LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', attestationType VARCHAR(255) NOT NULL, trust_path LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', aaguid LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', public_key VARBINARY(255) NOT NULL, user_handle VARCHAR(255) NOT NULL, counter INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE public_key_credentials');
    }
}
