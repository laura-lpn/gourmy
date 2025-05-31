<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531212102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE step ADD meals INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ADD position INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ALTER restaurant_id DROP NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step DROP meals
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step DROP position
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE step ALTER restaurant_id SET NOT NULL
        SQL);
    }
}
