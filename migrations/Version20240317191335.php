<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240317191335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE quest_completion_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE quest_completion_history (id INT NOT NULL, quest_id INT NOT NULL, user_id INT NOT NULL, complete_date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6B470949209E9EF4 ON quest_completion_history (quest_id)');
        $this->addSql('CREATE INDEX IDX_6B470949A76ED395 ON quest_completion_history (user_id)');
        $this->addSql('COMMENT ON COLUMN quest_completion_history.complete_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE quest_completion_history ADD CONSTRAINT FK_6B470949209E9EF4 FOREIGN KEY (quest_id) REFERENCES quest (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quest_completion_history ADD CONSTRAINT FK_6B470949A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_quest DROP CONSTRAINT fk_a1d5034fa76ed395');
        $this->addSql('ALTER TABLE user_quest DROP CONSTRAINT fk_a1d5034f209e9ef4');
        $this->addSql('DROP TABLE user_quest');
        $this->addSql('ALTER TABLE quest ALTER update_date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN quest.update_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE "user" ALTER update_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".update_date IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE quest_completion_history_id_seq CASCADE');
        $this->addSql('CREATE TABLE user_quest (user_id INT NOT NULL, quest_id INT NOT NULL, PRIMARY KEY(user_id, quest_id))');
        $this->addSql('CREATE INDEX idx_a1d5034f209e9ef4 ON user_quest (quest_id)');
        $this->addSql('CREATE INDEX idx_a1d5034fa76ed395 ON user_quest (user_id)');
        $this->addSql('ALTER TABLE user_quest ADD CONSTRAINT fk_a1d5034fa76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_quest ADD CONSTRAINT fk_a1d5034f209e9ef4 FOREIGN KEY (quest_id) REFERENCES quest (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quest_completion_history DROP CONSTRAINT FK_6B470949209E9EF4');
        $this->addSql('ALTER TABLE quest_completion_history DROP CONSTRAINT FK_6B470949A76ED395');
        $this->addSql('DROP TABLE quest_completion_history');
        $this->addSql('ALTER TABLE "user" ALTER update_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".update_date IS NULL');
        $this->addSql('ALTER TABLE quest ALTER update_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN quest.update_date IS NULL');
    }
}
