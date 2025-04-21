<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250421175403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant ADD uuid UUID NOT NULL');
        $this->addSql('ALTER TABLE restaurant ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE restaurant ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN restaurant.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN restaurant.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB95123FD17F50A6 ON restaurant (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_EB95123FD17F50A6');
        $this->addSql('ALTER TABLE restaurant DROP uuid');
        $this->addSql('ALTER TABLE restaurant DROP created_at');
        $this->addSql('ALTER TABLE restaurant DROP updated_at');
    }
}
