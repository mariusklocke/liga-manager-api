<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190224130821 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds index to events and unique index to match_days';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX IDX_5387574A87C03D1B ON events (occurred_at)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8B4D1496901F544EC001D133D1A3E7 ON match_days (number, season_id, tournament_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX IDX_5387574A87C03D1B ON events');
        $this->addSql('DROP INDEX UNIQ_8A8B4D1496901F544EC001D133D1A3E7 ON match_days');
    }
}
