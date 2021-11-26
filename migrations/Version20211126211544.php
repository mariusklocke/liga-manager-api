<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211126211544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add index to matches tables for column kickoff';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX IDX_62615BADD6E603D ON matches (kickoff)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX IDX_62615BADD6E603D ON matches');
    }
}
