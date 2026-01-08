<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to replace password field with registration_token in contact table
 * Security improvement: passwords should never be stored in plain text
 */
final class Version20260106000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace password field with registration_token in contact table for security';
    }

    public function up(Schema $schema): void
    {
        // Replace password column with registration_token column
        $this->addSql(<<<'SQL'
            ALTER TABLE contact CHANGE password registration_token VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Revert back to password column (not recommended for security reasons)
        $this->addSql(<<<'SQL'
            ALTER TABLE contact CHANGE registration_token password VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
