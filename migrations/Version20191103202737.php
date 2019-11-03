<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191103202737 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Increase length of id column for public_key_credentials table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE public_key_credentials CHANGE id id VARBINARY(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE public_key_credentials CHANGE id id VARBINARY(64) NOT NULL');
    }
}
