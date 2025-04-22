<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419200530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant DROP CONSTRAINT fk_eb95123f7e3c61f9');
        $this->addSql('DROP INDEX uniq_eb95123f7e3c61f9');
        $this->addSql('ALTER TABLE restaurant DROP owner_id');
        $this->addSql('ALTER TABLE "user" ADD restaurant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B1E7706E ON "user" (restaurant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649B1E7706E');
        $this->addSql('DROP INDEX UNIQ_8D93D649B1E7706E');
        $this->addSql('ALTER TABLE "user" DROP restaurant_id');
        $this->addSql('ALTER TABLE restaurant ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT fk_eb95123f7e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_eb95123f7e3c61f9 ON restaurant (owner_id)');
    }
}
