<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625125051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE app_user (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, confirmation_code VARCHAR(6) DEFAULT NULL, is_confirmed BOOLEAN NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_88BDF3E9E7927C74 ON app_user (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE attribute (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE brand (guid UUID NOT NULL, image_guid UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1C52F958F47645AE ON brand (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C52F958D955252C ON brand (image_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (guid UUID NOT NULL, parent_id UUID DEFAULT NULL, image_guid UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, publish_state VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, breadcrumbs JSON NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_64C19C1F47645AE ON category (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64C19C1D955252C ON category (image_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file (guid UUID NOT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, size INT NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (guid UUID NOT NULL, brand_guid UUID NOT NULL, main_image_guid UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, publish_state VARCHAR(50) NOT NULL, sku VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, breadcrumbs JSON NOT NULL, attributes JSON NOT NULL, price JSON NOT NULL, stock INT NOT NULL, rank INT NOT NULL, shor_description TEXT DEFAULT NULL, description TEXT DEFAULT NULL, composition TEXT DEFAULT NULL, how_to_use TEXT DEFAULT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(guid))
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
            CREATE INDEX IDX_1F1B251EE1AD514F ON item (main_image_guid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.sku IS 'Артикул товара'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.attributes IS 'Атрибуты товара (хранятся в jsonb)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.price IS 'Цены товара (хранятся в jsonb)'
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
            CREATE TABLE item_images (item_guid UUID NOT NULL, file_guid UUID NOT NULL, PRIMARY KEY(item_guid, file_guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_66E6CBA5E35F8B49 ON item_images (item_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_66E6CBA5A293A7DC ON item_images (file_guid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE brand ADD CONSTRAINT FK_1C52F958D955252C FOREIGN KEY (image_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (guid) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1D955252C FOREIGN KEY (image_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD CONSTRAINT FK_1F1B251EC08C50D FOREIGN KEY (brand_guid) REFERENCES brand (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD CONSTRAINT FK_1F1B251EE1AD514F FOREIGN KEY (main_image_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10AE35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10AA0F4B5F5 FOREIGN KEY (category_guid) REFERENCES category (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images ADD CONSTRAINT FK_66E6CBA5E35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images ADD CONSTRAINT FK_66E6CBA5A293A7DC FOREIGN KEY (file_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE brand DROP CONSTRAINT FK_1C52F958D955252C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP CONSTRAINT FK_64C19C1D955252C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP CONSTRAINT FK_1F1B251EC08C50D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP CONSTRAINT FK_1F1B251EE1AD514F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10AE35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10AA0F4B5F5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images DROP CONSTRAINT FK_66E6CBA5E35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images DROP CONSTRAINT FK_66E6CBA5A293A7DC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE app_user
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
            DROP TABLE file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_images
        SQL);
    }
}
