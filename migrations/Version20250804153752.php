<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804153752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP CONSTRAINT fk_794381c6fbf32840
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_794381c6fbf32840
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review RENAME COLUMN response_id TO original_review_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C654BDE98E FOREIGN KEY (original_review_id) REFERENCES review (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_794381C654BDE98E ON review (original_review_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SCHEMA gourmy
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP CONSTRAINT FK_794381C654BDE98E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_794381C654BDE98E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review RENAME COLUMN original_review_id TO response_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT fk_794381c6fbf32840 FOREIGN KEY (response_id) REFERENCES review (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_794381c6fbf32840 ON review (response_id)
        SQL);
    }
}
