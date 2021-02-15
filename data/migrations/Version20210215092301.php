<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210215092301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drops the no longer needed apiAuthorizationToken from the Setting table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting DROP apiAuthorizationToken');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting ADD apiAuthorizationToken TEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'The API authorization token used for this setting.\'');
    }
}
