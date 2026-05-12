<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512153756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD is_validated TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('UPDATE user SET is_validated = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP is_validated');
    }
}
