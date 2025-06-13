<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613104450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE attribute (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item_attribute (guid UUID NOT NULL, item_guid UUID NOT NULL, attribute_guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F6A0F90BE35F8B49 ON item_attribute (item_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F6A0F90B70876B3A ON item_attribute (attribute_guid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_attribute ADD CONSTRAINT FK_F6A0F90BE35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_attribute ADD CONSTRAINT FK_F6A0F90B70876B3A FOREIGN KEY (attribute_guid) REFERENCES attribute (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_attribute DROP CONSTRAINT FK_F6A0F90BE35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_attribute DROP CONSTRAINT FK_F6A0F90B70876B3A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE attribute
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_attribute
        SQL);
    }
}
