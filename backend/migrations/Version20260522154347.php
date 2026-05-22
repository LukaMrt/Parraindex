<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260522154347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person_link DROP FOREIGN KEY `FK_person_link_person`');
        $this->addSql('ALTER TABLE person_link ADD CONSTRAINT FK_BC4A1DDA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person_link RENAME INDEX idx_person_link_person TO IDX_BC4A1DDA217BBB47');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person_link DROP FOREIGN KEY FK_BC4A1DDA217BBB47');
        $this->addSql('ALTER TABLE person_link ADD CONSTRAINT `FK_person_link_person` FOREIGN KEY (person_id) REFERENCES person (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_link RENAME INDEX idx_bc4a1dda217bbb47 TO IDX_person_link_person');
    }
}
