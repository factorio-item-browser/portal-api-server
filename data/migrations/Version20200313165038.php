<?php declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adding Combination table, adding "name" to the Setting table.
 */
final class Version20200313165038 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Combination (id BINARY(16) NOT NULL COMMENT \'The ID of the combination.(DC2Type:uuid_binary)\', modNames JSON NOT NULL COMMENT \'The mod names used for this setting.\', isAvailable TINYINT(1) NOT NULL COMMENT \'Whether the data for the combination is already available..\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'The table holding the combinations.\' ');
        $this->addSql('ALTER TABLE Setting ADD name VARCHAR(255) NOT NULL COMMENT \'The name of the setting.\' AFTER combinationId, DROP modNames, CHANGE combinationId combinationId BINARY(16) NOT NULL COMMENT \'The ID of the combination.(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE Setting ADD CONSTRAINT FK_50C98104FE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('CREATE INDEX IDX_50C98104FE40C4A7 ON Setting (combinationId)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting DROP FOREIGN KEY FK_50C98104FE40C4A7');
        $this->addSql('DROP TABLE Combination');
        $this->addSql('DROP INDEX IDX_50C98104FE40C4A7 ON Setting');
        $this->addSql('ALTER TABLE Setting ADD modNames JSON NOT NULL COMMENT \'The mod names used for this setting.\', DROP name, CHANGE combinationId combinationId BINARY(16) NOT NULL COMMENT \'The ID of the combination used for this setting.(DC2Type:uuid_binary)\'');
    }
}
