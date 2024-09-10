<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910110931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characteristic (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, person_id INT NOT NULL, value VARCHAR(255) NOT NULL, visible TINYINT(1) NOT NULL, INDEX IDX_522FA950C54C8C93 (type_id), INDEX IDX_522FA950217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE characteristic_type (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type INT NOT NULL, url VARCHAR(255) DEFAULT NULL, image VARCHAR(255) NOT NULL, place INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE characteristic ADD CONSTRAINT FK_522FA950C54C8C93 FOREIGN KEY (type_id) REFERENCES characteristic_type (id)');
        $this->addSql('ALTER TABLE characteristic ADD CONSTRAINT FK_522FA950217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE characteristic DROP FOREIGN KEY FK_522FA950C54C8C93');
        $this->addSql('ALTER TABLE characteristic DROP FOREIGN KEY FK_522FA950217BBB47');
        $this->addSql('DROP TABLE characteristic');
        $this->addSql('DROP TABLE characteristic_type');
    }
}
