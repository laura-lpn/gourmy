<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715083013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_favorite_roadtrips (user_id INT NOT NULL, roadtrip_id INT NOT NULL, PRIMARY KEY(user_id, roadtrip_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8536B359A76ED395 ON user_favorite_roadtrips (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8536B359CA4CCFF5 ON user_favorite_roadtrips (roadtrip_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_roadtrips ADD CONSTRAINT FK_8536B359A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_roadtrips ADD CONSTRAINT FK_8536B359CA4CCFF5 FOREIGN KEY (roadtrip_id) REFERENCES roadtrip (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_roadtrips DROP CONSTRAINT FK_8536B359A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favorite_roadtrips DROP CONSTRAINT FK_8536B359CA4CCFF5
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_favorite_roadtrips
        SQL);
    }
}
