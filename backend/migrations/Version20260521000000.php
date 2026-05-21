<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add association entity and person_association join table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE association (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE person_association (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, association_id INT NOT NULL, poste VARCHAR(255) NOT NULL, INDEX IDX_906E7E44217BBB47 (person_id), INDEX IDX_906E7E44EFB9C8A5 (association_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE person_association ADD CONSTRAINT FK_906E7E44217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person_association ADD CONSTRAINT FK_906E7E44EFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person_association DROP FOREIGN KEY FK_906E7E44217BBB47');
        $this->addSql('ALTER TABLE person_association DROP FOREIGN KEY FK_906E7E44EFB9C8A5');
        $this->addSql('DROP TABLE person_association');
        $this->addSql('DROP TABLE association');
    }
}
