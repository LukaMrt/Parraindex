<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260513060709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP color');
        $this->addSql('DROP INDEX IDX_818CC9D4299416C9 ON sponsor');
        $this->addSql('DROP INDEX IDX_818CC9D4487CFC8E ON sponsor');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD color VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_818CC9D4299416C9 ON sponsor (god_child_id)');
        $this->addSql('CREATE INDEX IDX_818CC9D4487CFC8E ON sponsor (god_father_id)');
    }
}
