<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200815075008 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE User DROP FOREIGN KEY FK_2DA1797767D65AC3');
        $this->addSql('DROP INDEX UNIQ_2DA1797767D65AC3 ON User');
        $this->addSql('ALTER TABLE User DROP currentSettingId');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE User ADD currentSettingId BINARY(16) DEFAULT NULL COMMENT \'The ID of the setting.(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE User ADD CONSTRAINT FK_2DA1797767D65AC3 FOREIGN KEY (currentSettingId) REFERENCES Setting (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2DA1797767D65AC3 ON User (currentSettingId)');
    }
}
