<?php declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Extending combination table with export related columns.
 */
final class Version20200330100127 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Combination ADD status ENUM(\'pending\',\'available\',\'errored\') NOT NULL COMMENT \'The status of the combination.(DC2Type:enum_combination_status)\', ADD exportTime TIMESTAMP NULL DEFAULT NULL COMMENT \'The timestamp of export of the combination.(DC2Type:timestamp)\', ADD lastCheckTime TIMESTAMP NULL DEFAULT NULL COMMENT \'The timestamp when the combination was last checked.(DC2Type:timestamp)\', DROP isAvailable');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Combination ADD isAvailable TINYINT(1) NOT NULL COMMENT \'Whether the data for the combination is already available..\', DROP status, DROP exportTime, DROP lastCheckTime');
    }
}
