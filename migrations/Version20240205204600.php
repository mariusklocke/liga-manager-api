<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205204600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop comments on various columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `events` CHANGE `payload` `payload` LONGTEXT NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `events` CHANGE `occurred_at` `occurred_at` DATETIME NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `match_days` CHANGE `start_date` `start_date` DATE NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `match_days` CHANGE `end_date` `end_date` DATE NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `matches` CHANGE `kickoff` `kickoff` DATETIME NULL COMMENT ''");
        $this->addSql("ALTER TABLE `matches` CHANGE `cancelled_at` `cancelled_at` DATETIME NULL COMMENT ''");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `transports` `transports` LONGTEXT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `trust_path` `trust_path` LONGTEXT NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `aaguid` `aaguid` LONGTEXT NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `updated_at` `updated_at` DATETIME NULL COMMENT ''");
        $this->addSql("ALTER TABLE `ranking_penalties` CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `rankings` CHANGE `updated_at` `updated_at` DATETIME NULL COMMENT ''");
        $this->addSql("ALTER TABLE `teams` CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE `users` CHANGE `last_password_change` `last_password_change` DATETIME NULL COMMENT ''");
        $this->addSql("ALTER TABLE `users` CHANGE `last_token_invalidation` `last_token_invalidation` DATETIME NULL COMMENT ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `events` CHANGE `payload` `payload` LONGTEXT NOT NULL COMMENT '(DC2Type:array)'");
        $this->addSql("ALTER TABLE `events` CHANGE `occurred_at` `occurred_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `match_days` CHANGE `start_date` `start_date` DATE NOT NULL COMMENT '(DC2Type:date_immutable)'");
        $this->addSql("ALTER TABLE `match_days` CHANGE `end_date` `end_date` DATE NOT NULL COMMENT '(DC2Type:date_immutable)'");
        $this->addSql("ALTER TABLE `matches` CHANGE `kickoff` `kickoff` DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `matches` CHANGE `cancelled_at` `cancelled_at` DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `transports` `transports` LONGTEXT NULL COMMENT '(DC2Type:json)'");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `trust_path` `trust_path` LONGTEXT NOT NULL COMMENT '(DC2Type:object)'");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `aaguid` `aaguid` LONGTEXT NOT NULL COMMENT '(DC2Type:object)'");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `updated_at` `updated_at` DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `ranking_penalties` CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `rankings` CHANGE `updated_at` `updated_at` DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `teams` CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `users` CHANGE `last_password_change` `last_password_change` DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE `users` CHANGE `last_token_invalidation` `last_token_invalidation` DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
    }
}
