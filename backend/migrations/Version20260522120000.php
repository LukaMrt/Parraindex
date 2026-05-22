<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add person_link table for free-form links on a person profile';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE person_link (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(2048) NOT NULL, INDEX IDX_person_link_person (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE person_link ADD CONSTRAINT FK_person_link_person FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person_link DROP FOREIGN KEY FK_person_link_person');
        $this->addSql('DROP TABLE person_link');
    }
}
