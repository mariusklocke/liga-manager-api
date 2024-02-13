<?php
declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240209201211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Rename unique indexes for match_days and users';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('DROP INDEX UNIQ_8A8B4D1496901F544EC001D133D1A3E7 ON match_days');
        $this->addSql('CREATE UNIQUE INDEX unique_number ON match_days (number, season_id, tournament_id)');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74 ON users');
        $this->addSql('CREATE UNIQUE INDEX unique_email ON users (email)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX unique_number ON match_days');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8B4D1496901F544EC001D133D1A3E7 ON match_days (number, season_id, tournament_id)');
        $this->addSql('DROP INDEX unique_email ON users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }
}
