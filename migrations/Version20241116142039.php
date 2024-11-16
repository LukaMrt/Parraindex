<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241116142039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characteristic (id INT AUTO_INCREMENT NOT NULL, value VARCHAR(255) NOT NULL, visible TINYINT(1) NOT NULL, characteristic_type_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_522FA950823823DD (characteristic_type_id), INDEX IDX_522FA950217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE characteristic_type (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type INT NOT NULL, url VARCHAR(255) DEFAULT NULL, image VARCHAR(255) NOT NULL, place INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, contacter_name VARCHAR(255) NOT NULL, contacter_email VARCHAR(255) NOT NULL, type INT NOT NULL, description VARCHAR(255) NOT NULL, resolution_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, person_id INT DEFAULT NULL, sponsor_id INT DEFAULT NULL, INDEX IDX_4C62E638217BBB47 (person_id), INDEX IDX_4C62E63812F7FB51 (sponsor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, picture VARCHAR(255) DEFAULT NULL, birthdate DATE DEFAULT NULL, biography VARCHAR(255) DEFAULT NULL, color VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, start_year INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, type INT NOT NULL, created_at DATETIME NOT NULL, god_father_id INT NOT NULL, god_child_id INT NOT NULL, INDEX IDX_818CC9D4487CFC8E (god_father_id), INDEX IDX_818CC9D4299416C9 (god_child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, person_id INT NOT NULL, UNIQUE INDEX UNIQ_8D93D649217BBB47 (person_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE characteristic ADD CONSTRAINT FK_522FA950823823DD FOREIGN KEY (characteristic_type_id) REFERENCES characteristic_type (id)');
        $this->addSql('ALTER TABLE characteristic ADD CONSTRAINT FK_522FA950217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63812F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id)');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D4487CFC8E FOREIGN KEY (god_father_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D4299416C9 FOREIGN KEY (god_child_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE characteristic DROP FOREIGN KEY FK_522FA950823823DD');
        $this->addSql('ALTER TABLE characteristic DROP FOREIGN KEY FK_522FA950217BBB47');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638217BBB47');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E63812F7FB51');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D4487CFC8E');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D4299416C9');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649217BBB47');
        $this->addSql('DROP TABLE characteristic');
        $this->addSql('DROP TABLE characteristic_type');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE sponsor');
        $this->addSql('DROP TABLE user');
    }
}
