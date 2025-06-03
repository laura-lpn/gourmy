<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531204808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE roadtrip_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE step_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE roadtrip (id INT NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_EA152DDF675F31B ON roadtrip (author_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE step (id INT NOT NULL, restaurant_id INT NOT NULL, roadtrip_id INT NOT NULL, town VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_43B9FE3CB1E7706E ON step (restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_43B9FE3CCA4CCFF5 ON step (roadtrip_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roadtrip ADD CONSTRAINT FK_EA152DDF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ADD CONSTRAINT FK_43B9FE3CB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ADD CONSTRAINT FK_43B9FE3CCA4CCFF5 FOREIGN KEY (roadtrip_id) REFERENCES roadtrip (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE roadtrip_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE step_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roadtrip DROP CONSTRAINT FK_EA152DDF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step DROP CONSTRAINT FK_43B9FE3CB1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step DROP CONSTRAINT FK_43B9FE3CCA4CCFF5
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE roadtrip
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE step
        SQL);
    }
}
