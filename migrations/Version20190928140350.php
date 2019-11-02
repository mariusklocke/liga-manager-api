<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190928140350 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Makes user password optional';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(255) NOT NULL');
    }
}
