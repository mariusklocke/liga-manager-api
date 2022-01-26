<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220126171550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update comment for compatibility with Doctrine DBAL 3.x';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `transports` `transports` LONGTEXT COMMENT '(DC2Type:json)';");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `public_key_credentials` CHANGE `transports` `transports` LONGTEXT COMMENT '(DC2Type:json_array)';");
    }
}
