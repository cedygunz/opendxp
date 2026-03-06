<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\CoreBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305140825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
         $this->addSql('ALTER TABLE sites ADD customSettings text NULL');
    }

    public function down(Schema $schema): void
    {
        // do nothing
    }
}
