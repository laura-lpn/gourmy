<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715084706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_favorite_restaurants (user_id INT NOT NULL, restaurant_id INT NOT NULL, PRIMARY KEY(user_id, restaurant_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D0B0E118A76ED395 ON user_favorite_restaurants (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D0B0E118B1E7706E ON user_favorite_restaurants (restaurant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_restaurants ADD CONSTRAINT FK_D0B0E118A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_restaurants ADD CONSTRAINT FK_D0B0E118B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_restaurants DROP CONSTRAINT FK_D0B0E118A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_restaurants DROP CONSTRAINT FK_D0B0E118B1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_favorite_restaurants
        SQL);
    }
}
