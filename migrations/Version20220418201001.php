<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220418201001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make pitch location latitude/longitude nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `pitches` MODIFY `location_longitude` DOUBLE NULL;");
        $this->addSql("ALTER TABLE `pitches` MODIFY `location_latitude` DOUBLE NULL;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `pitches` MODIFY `location_longitude` DOUBLE NOT NULL;");
        $this->addSql("ALTER TABLE `pitches` MODIFY `location_latitude` DOUBLE NOT NULL;");
    }
}
