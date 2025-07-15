<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715103042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE restaurant_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE restaurant_image (id INT NOT NULL, restaurant_id INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8F20B78ED17F50A6 ON restaurant_image (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F20B78EB1E7706E ON restaurant_image (restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN restaurant_image.uuid IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN restaurant_image.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_image ADD CONSTRAINT FK_8F20B78EB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE restaurant_image_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_image DROP CONSTRAINT FK_8F20B78EB1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE restaurant_image
        SQL);
    }
}
