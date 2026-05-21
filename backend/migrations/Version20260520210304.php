<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520210304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add color to filiere, add logo to school';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE filiere ADD color VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE school ADD logo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE filiere DROP color');
        $this->addSql('ALTER TABLE school DROP COLUMN logo');
    }
}
