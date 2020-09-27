<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds a usage timestamp and a temporary flag to the setting table.
 */
final class Version20200805130258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting ADD lastUsageTime TIMESTAMP NOT NULL COMMENT \'The time when the setting was last used.(DC2Type:timestamp)\' AFTER recipeMode, ADD isTemporary TINYINT(1) NOT NULL COMMENT \'Whether the setting is only temporary.\' AFTER hasData');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting DROP lastUsageTime, DROP isTemporary');
    }
}
