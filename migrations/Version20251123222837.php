<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123222837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trade_ticket (id SERIAL NOT NULL, user_id INT NOT NULL, partner_id INT DEFAULT NULL, status VARCHAR(16) DEFAULT \'waiting\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, matched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AE8913B1A76ED395 ON trade_ticket (user_id)');
        $this->addSql('CREATE INDEX IDX_AE8913B19393F8FE ON trade_ticket (partner_id)');
        $this->addSql('COMMENT ON COLUMN trade_ticket.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN trade_ticket.matched_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE trade_ticket ADD CONSTRAINT FK_AE8913B1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_ticket ADD CONSTRAINT FK_AE8913B19393F8FE FOREIGN KEY (partner_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_spell ALTER uuid TYPE UUID');
        $this->addSql('COMMENT ON COLUMN user_spell.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER INDEX uniq_user_spell_uuid RENAME TO UNIQ_32FD36D5D17F50A6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE trade_ticket DROP CONSTRAINT FK_AE8913B1A76ED395');
        $this->addSql('ALTER TABLE trade_ticket DROP CONSTRAINT FK_AE8913B19393F8FE');
        $this->addSql('DROP TABLE trade_ticket');
        $this->addSql('ALTER TABLE user_spell ALTER uuid TYPE UUID');
        $this->addSql('COMMENT ON COLUMN user_spell.uuid IS NULL');
        $this->addSql('ALTER INDEX uniq_32fd36d5d17f50a6 RENAME TO uniq_user_spell_uuid');
    }
}
