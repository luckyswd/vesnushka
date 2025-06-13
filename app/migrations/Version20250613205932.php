<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613205932 extends AbstractMigration
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
            CREATE TABLE brand (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1C52F958F47645AE ON brand (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (guid UUID NOT NULL, parent_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, publish_state VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, breadcrumbs JSON NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_64C19C1F47645AE ON category (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (guid UUID NOT NULL, brand_guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, publish_state VARCHAR(50) NOT NULL, sku VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, breadcrumbs JSON NOT NULL, attributes JSON NOT NULL, stock INT NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1F1B251EF9038C4 ON item (sku)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1F1B251EF47645AE ON item (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251EC08C50D ON item (brand_guid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.sku IS 'Артикул товара'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.attributes IS 'Атрибуты товара (хранятся в jsonb)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item_category (item_guid UUID NOT NULL, category_guid UUID NOT NULL, PRIMARY KEY(item_guid, category_guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41D10AE35F8B49 ON item_category (item_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41D10AA0F4B5F5 ON item_category (category_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item_price (guid UUID NOT NULL, item_guid UUID NOT NULL, price_type VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E06F3909E35F8B49 ON item_price (item_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (guid) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD CONSTRAINT FK_1F1B251EC08C50D FOREIGN KEY (brand_guid) REFERENCES brand (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10AE35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10AA0F4B5F5 FOREIGN KEY (category_guid) REFERENCES category (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_price ADD CONSTRAINT FK_E06F3909E35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP CONSTRAINT FK_1F1B251EC08C50D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10AE35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10AA0F4B5F5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_price DROP CONSTRAINT FK_E06F3909E35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE attribute
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE brand
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_price
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
