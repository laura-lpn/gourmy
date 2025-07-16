<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715145930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE restaurant_charter_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE restaurant_charter (id INT NOT NULL, restaurant_id INT NOT NULL, uses_local_products BOOLEAN NOT NULL, homemade_cuisine BOOLEAN NOT NULL, waste_reduction BOOLEAN NOT NULL, transparent_origin BOOLEAN NOT NULL, professional_replies_to_reviews BOOLEAN NOT NULL, accepts_moderation BOOLEAN NOT NULL, validated_by_moderator BOOLEAN DEFAULT false NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_99C321D0D17F50A6 ON restaurant_charter (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_99C321D0B1E7706E ON restaurant_charter (restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN restaurant_charter.uuid IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN restaurant_charter.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_charter ADD CONSTRAINT FK_99C321D0B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE restaurant_charter_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant_charter DROP CONSTRAINT FK_99C321D0B1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE restaurant_charter
        SQL);
    }
}
