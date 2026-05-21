<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520204549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add school entity and link to person_filiere';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE school (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE person_filiere ADD school_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person_filiere ADD CONSTRAINT FK_C4EEE17C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('CREATE INDEX IDX_C4EEE17C32A47EE ON person_filiere (school_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE school');
        $this->addSql('ALTER TABLE person_filiere DROP FOREIGN KEY FK_C4EEE17C32A47EE');
        $this->addSql('DROP INDEX IDX_C4EEE17C32A47EE ON person_filiere');
        $this->addSql('ALTER TABLE person_filiere DROP school_id');
    }
}
