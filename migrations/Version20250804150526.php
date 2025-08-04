<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804150526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE step_restaurant (step_id INT NOT NULL, restaurant_id INT NOT NULL, PRIMARY KEY(step_id, restaurant_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7FE347E73B21E9C ON step_restaurant (step_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7FE347EB1E7706E ON step_restaurant (restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_restaurant ADD CONSTRAINT FK_E7FE347E73B21E9C FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_restaurant ADD CONSTRAINT FK_E7FE347EB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step DROP CONSTRAINT fk_43b9fe3cb1e7706e
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_43b9fe3cb1e7706e
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step DROP restaurant_id
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
            ALTER TABLE step_restaurant DROP CONSTRAINT FK_E7FE347E73B21E9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_restaurant DROP CONSTRAINT FK_E7FE347EB1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE step_restaurant
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ADD restaurant_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ADD CONSTRAINT fk_43b9fe3cb1e7706e FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_43b9fe3cb1e7706e ON step (restaurant_id)
        SQL);
    }
}
