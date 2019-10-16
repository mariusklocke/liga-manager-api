<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190101000000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Initial schema';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE events (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, occurred_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', payload LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE match_days (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, season_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, tournament_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, number INT NOT NULL, start_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_8A8B4D1433D1A3E7 (tournament_id), INDEX IDX_8A8B4D144EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE matches (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, match_day_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, home_team_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, guest_team_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, pitch_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, kickoff DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', cancelled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', cancellation_reason VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, home_score INT DEFAULT NULL, guest_score INT DEFAULT NULL, INDEX IDX_62615BA9C4C13F6 (home_team_id), INDEX IDX_62615BAA8ADB827 (match_day_id), INDEX IDX_62615BAFEEFC64B (pitch_id), INDEX IDX_62615BA69A91CE2 (guest_team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE pitches (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, `label` VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, location_longitude DOUBLE PRECISION NOT NULL, location_latitude DOUBLE PRECISION NOT NULL, contact_first_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, contact_last_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, contact_phone VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, contact_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ranking_penalties (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, season_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, team_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, reason VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, points INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A5850F92296CD8AE (team_id), INDEX IDX_A5850F924EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ranking_positions (season_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, team_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, sort_index INT NOT NULL, number INT NOT NULL, matches INT NOT NULL, wins INT NOT NULL, draws INT NOT NULL, losses INT NOT NULL, scored_goals INT NOT NULL, conceded_goals INT NOT NULL, points INT NOT NULL, INDEX IDX_CB859A414EC001D1 (season_id), INDEX IDX_CB859A4130F55073 (sort_index), INDEX IDX_CB859A41296CD8AE (team_id), PRIMARY KEY(season_id, team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE rankings (season_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(season_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE seasons (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, state VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, match_day_count INT NOT NULL, team_count INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE seasons_teams_link (season_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, team_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_EA130B604EC001D1 (season_id), INDEX IDX_EA130B60296CD8AE (team_id), PRIMARY KEY(season_id, team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE teams (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', contact_first_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, contact_last_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, contact_phone VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, contact_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tournaments (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, rounds INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, password VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, last_password_change DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', role VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, first_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, last_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users_teams_link (user_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, team_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_B8ACA73A76ED395 (user_id), INDEX IDX_B8ACA73296CD8AE (team_id), PRIMARY KEY(user_id, team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE match_days');
        $this->addSql('DROP TABLE matches');
        $this->addSql('DROP TABLE pitches');
        $this->addSql('DROP TABLE ranking_penalties');
        $this->addSql('DROP TABLE ranking_positions');
        $this->addSql('DROP TABLE rankings');
        $this->addSql('DROP TABLE seasons');
        $this->addSql('DROP TABLE seasons_teams_link');
        $this->addSql('DROP TABLE teams');
        $this->addSql('DROP TABLE tournaments');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE users_teams_link');
    }
}
