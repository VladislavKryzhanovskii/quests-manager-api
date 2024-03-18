<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240317010456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_quest (user_id INT NOT NULL, quest_id INT NOT NULL, PRIMARY KEY(user_id, quest_id))');
        $this->addSql('CREATE INDEX IDX_A1D5034FA76ED395 ON user_quest (user_id)');
        $this->addSql('CREATE INDEX IDX_A1D5034F209E9EF4 ON user_quest (quest_id)');
        $this->addSql('ALTER TABLE user_quest ADD CONSTRAINT FK_A1D5034FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_quest ADD CONSTRAINT FK_A1D5034F209E9EF4 FOREIGN KEY (quest_id) REFERENCES quest (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_quest DROP CONSTRAINT FK_A1D5034FA76ED395');
        $this->addSql('ALTER TABLE user_quest DROP CONSTRAINT FK_A1D5034F209E9EF4');
        $this->addSql('DROP TABLE user_quest');
    }
}
