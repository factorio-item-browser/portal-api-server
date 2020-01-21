<?php declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial setup of the database.
 */
final class Version20200121175613 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE User ( id BINARY(16) NOT NULL COMMENT \'The ID of the user.(DC2Type:uuid_binary)\', locale VARCHAR(5) NOT NULL COMMENT \'The locale used by the user.\', lastVisitTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'The time when the user last visited.\', currentSettingId BINARY(16) DEFAULT NULL COMMENT \'The ID of the setting.(DC2Type:uuid_binary)\', UNIQUE INDEX UNIQ_2DA1797767D65AC3 (currentSettingId), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'The table holding the users of the portal.\'');
        $this->addSql('CREATE TABLE Setting ( id BINARY(16) NOT NULL COMMENT \'The ID of the setting.(DC2Type:uuid_binary)\', userId BINARY(16) NOT NULL COMMENT \'The ID of the user.(DC2Type:uuid_binary)\', combinationId BINARY(16) NOT NULL COMMENT \'The ID of the combination used for this setting.(DC2Type:uuid_binary)\', modNames JSON NOT NULL COMMENT \'The mod names used for this setting.\', recipeMode ENUM(\'hybrid\', \'normal\', \'expensive\') NOT NULL COMMENT \'The recipe mode used for this setting.(DC2Type:enum_recipe_mode)\', apiAuthorizationToken TEXT NOT NULL COMMENT \'The API authorization token used for this setting.\', INDEX IDX_50C9810464B64DCC (userId), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'The table holding the settings of the users.\'');
        $this->addSql('CREATE TABLE SidebarEntity ( settingId BINARY(16) NOT NULL COMMENT \'The ID of the setting.(DC2Type:uuid_binary)\', type ENUM(\'item\', \'fluid\', \'recipe\') NOT NULL COMMENT \'The type of the sidebar entity.(DC2Type:enum_sidebar_entity_type)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the sidebar entity.\', label TEXT NOT NULL COMMENT \'The translated label of the sidebar entity.\', pinnedPosition INT UNSIGNED NOT NULL COMMENT \'The pinned position of the entity in the sidebar. 0 if not pinned.\', lastViewTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'The time when the entity was last viewed.\', INDEX IDX_746949D2569FE72A (settingId), PRIMARY KEY(settingId, type, name) ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'The table holding the sidebar entities.\'');
        $this->addSql('ALTER TABLE User ADD CONSTRAINT FK_2DA1797767D65AC3 FOREIGN KEY (currentSettingId) REFERENCES Setting (id)');
        $this->addSql('ALTER TABLE Setting ADD CONSTRAINT FK_50C9810464B64DCC FOREIGN KEY (userId) REFERENCES User (id)');
        $this->addSql('ALTER TABLE SidebarEntity ADD CONSTRAINT FK_746949D2569FE72A FOREIGN KEY (settingId) REFERENCES Setting (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting DROP FOREIGN KEY FK_50C9810464B64DCC');
        $this->addSql('ALTER TABLE User DROP FOREIGN KEY FK_2DA1797767D65AC3');
        $this->addSql('ALTER TABLE SidebarEntity DROP FOREIGN KEY FK_746949D2569FE72A');
        $this->addSql('DROP TABLE User');
        $this->addSql('DROP TABLE Setting');
        $this->addSql('DROP TABLE SidebarEntity');
    }
}
