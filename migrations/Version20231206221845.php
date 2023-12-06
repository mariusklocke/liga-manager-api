<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231206221845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add team logos';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `teams` ADD COLUMN `logo_id` VARCHAR(255) DEFAULT NULL;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `teams` DROP COLUMN `logo_id`;");
    }
}
