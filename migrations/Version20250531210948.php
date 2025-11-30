<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531210948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE step_type_restaurant (step_id INT NOT NULL, type_restaurant_id INT NOT NULL, PRIMARY KEY(step_id, type_restaurant_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FE6B2ECC73B21E9C ON step_type_restaurant (step_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FE6B2ECCC10AC75B ON step_type_restaurant (type_restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_type_restaurant ADD CONSTRAINT FK_FE6B2ECC73B21E9C FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_type_restaurant ADD CONSTRAINT FK_FE6B2ECCC10AC75B FOREIGN KEY (type_restaurant_id) REFERENCES type_restaurant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_type_restaurant DROP CONSTRAINT FK_FE6B2ECC73B21E9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step_type_restaurant DROP CONSTRAINT FK_FE6B2ECCC10AC75B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE step_type_restaurant
        SQL);
    }
}
