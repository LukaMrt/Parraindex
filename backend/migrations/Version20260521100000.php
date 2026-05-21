<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add diploma_name to person_filiere, add start_date and end_date to person_association';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person_filiere ADD diploma_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE person_association ADD start_date DATE DEFAULT NULL, ADD end_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person_filiere DROP diploma_name');
        $this->addSql('ALTER TABLE person_association DROP start_date, DROP end_date');
    }
}
