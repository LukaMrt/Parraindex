<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511193802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add updatedAt field to Person for VichUploader change detection';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person DROP updated_at');
    }
}
