<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250608192537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (guid UUID NOT NULL, parent_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, publish_state VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_64C19C1F47645AE ON category (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, publish_state VARCHAR(50) NOT NULL, sku VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1F1B251EF9038C4 ON item (sku)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1F1B251EF47645AE ON item (url)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.sku IS 'Артикул товара'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item_category (item_id UUID NOT NULL, category_id UUID NOT NULL, PRIMARY KEY(item_id, category_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41D10A126F525E ON item_category (item_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41D10A12469DE2 ON item_category (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (guid) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10A126F525E FOREIGN KEY (item_id) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10A12469DE2 FOREIGN KEY (category_id) REFERENCES category (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
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
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10A126F525E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10A12469DE2
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
    }
}
