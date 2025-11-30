<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531191304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE type_restaurant_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE restaurant_type_restaurant (restaurant_id INT NOT NULL, type_restaurant_id INT NOT NULL, PRIMARY KEY(restaurant_id, type_restaurant_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3069D2F8B1E7706E ON restaurant_type_restaurant (restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3069D2F8C10AC75B ON restaurant_type_restaurant (type_restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_restaurant (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_type_restaurant ADD CONSTRAINT FK_3069D2F8B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_type_restaurant ADD CONSTRAINT FK_3069D2F8C10AC75B FOREIGN KEY (type_restaurant_id) REFERENCES type_restaurant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE type_restaurant_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_type_restaurant DROP CONSTRAINT FK_3069D2F8B1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_type_restaurant DROP CONSTRAINT FK_3069D2F8C10AC75B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE restaurant_type_restaurant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_restaurant
        SQL);
    }
}
