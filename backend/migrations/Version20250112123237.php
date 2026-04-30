<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250112123237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E63812F7FB51');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638217BBB47');
        $this->addSql('DROP INDEX IDX_4C62E63812F7FB51 ON contact');
        $this->addSql('DROP INDEX IDX_4C62E638217BBB47 ON contact');
        $this->addSql('ALTER TABLE contact ADD contacter_last_name VARCHAR(255) NOT NULL, ADD related_person_first_name VARCHAR(255) DEFAULT NULL, ADD related_person_last_name VARCHAR(255) DEFAULT NULL, ADD related_person2_first_name VARCHAR(255) DEFAULT NULL, ADD entry_year INT DEFAULT NULL, ADD related_person2_last_name VARCHAR(255) DEFAULT NULL, ADD sponsor_type INT NOT NULL, ADD sponsor_date DATETIME DEFAULT NULL, ADD password VARCHAR(255) DEFAULT NULL, DROP person_id, DROP sponsor_id, CHANGE contacter_name contacter_first_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact ADD contacter_name VARCHAR(255) NOT NULL, ADD sponsor_id INT DEFAULT NULL, DROP contacter_first_name, DROP contacter_last_name, DROP related_person_first_name, DROP related_person_last_name, DROP related_person2_first_name, DROP related_person2_last_name, DROP sponsor_type, DROP sponsor_date, DROP password, CHANGE entry_year person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63812F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4C62E63812F7FB51 ON contact (sponsor_id)');
        $this->addSql('CREATE INDEX IDX_4C62E638217BBB47 ON contact (person_id)');
    }
}
