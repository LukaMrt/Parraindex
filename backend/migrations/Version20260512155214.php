<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512155214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_contact_resolution ON contact (resolution_date)');
        $this->addSql('CREATE INDEX idx_contact_type ON contact (type)');
        $this->addSql('CREATE INDEX idx_person_start_year ON person (start_year)');
        $this->addSql('CREATE INDEX idx_person_name ON person (first_name, last_name)');
        $this->addSql('ALTER TABLE sponsor RENAME INDEX idx_818cc9d4487cfc8e TO idx_sponsor_godfather');
        $this->addSql('ALTER TABLE sponsor RENAME INDEX idx_818cc9d4299416c9 TO idx_sponsor_godchild');
        $this->addSql('ALTER TABLE user CHANGE is_validated is_validated TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_contact_resolution ON contact');
        $this->addSql('DROP INDEX idx_contact_type ON contact');
        $this->addSql('DROP INDEX idx_person_start_year ON person');
        $this->addSql('DROP INDEX idx_person_name ON person');
        $this->addSql('ALTER TABLE sponsor RENAME INDEX idx_sponsor_godfather TO IDX_818CC9D4487CFC8E');
        $this->addSql('ALTER TABLE sponsor RENAME INDEX idx_sponsor_godchild TO IDX_818CC9D4299416C9');
        $this->addSql('ALTER TABLE user CHANGE is_validated is_validated TINYINT DEFAULT 1 NOT NULL');
    }
}
