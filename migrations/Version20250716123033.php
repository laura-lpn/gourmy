<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716123033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE badge_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE badge (id INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, background_color VARCHAR(50) NOT NULL, condition_value INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_badges (user_id INT NOT NULL, badge_id INT NOT NULL, PRIMARY KEY(user_id, badge_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1DA448A7A76ED395 ON user_badges (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1DA448A7F7A2C2FC ON user_badges (badge_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badges ADD CONSTRAINT FK_1DA448A7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badges ADD CONSTRAINT FK_1DA448A7F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD points INT DEFAULT 0 NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE badge_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badges DROP CONSTRAINT FK_1DA448A7A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badges DROP CONSTRAINT FK_1DA448A7F7A2C2FC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badge
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_badges
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP points
        SQL);
    }
}
