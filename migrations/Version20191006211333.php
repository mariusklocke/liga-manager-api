<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191006211333 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add last token invalidation date column to user table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users ADD last_token_invalidation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users DROP last_token_invalidation');
    }
}
