<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260108080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add database indexes for frequently queried columns to improve performance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_person_name ON person (first_name, last_name)');
        $this->addSql('CREATE INDEX idx_person_start_year ON person (start_year)');
        $this->addSql('CREATE INDEX idx_sponsor_godfather ON sponsor (god_father_id)');
        $this->addSql('CREATE INDEX idx_sponsor_godchild ON sponsor (god_child_id)');
        $this->addSql('CREATE INDEX idx_contact_type ON contact (type)');
        $this->addSql('CREATE INDEX idx_contact_resolution ON contact (resolution_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_person_name ON person');
        $this->addSql('DROP INDEX idx_person_start_year ON person');
        $this->addSql('DROP INDEX idx_sponsor_godfather ON sponsor');
        $this->addSql('DROP INDEX idx_sponsor_godchild ON sponsor');
        $this->addSql('DROP INDEX idx_contact_type ON contact');
        $this->addSql('DROP INDEX idx_contact_resolution ON contact');
    }
}
