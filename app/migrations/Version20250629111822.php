<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629111822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE cart (guid UUID NOT NULL, user_guid UUID DEFAULT NULL, currency VARCHAR(50) NOT NULL, total_amount VARCHAR(255) DEFAULT '0' NOT NULL, active BOOLEAN DEFAULT true NOT NULL, session_token VARCHAR(36) DEFAULT NULL, delivery_method VARCHAR(50) DEFAULT NULL, delivery_cost BIGINT DEFAULT 0 NOT NULL, payment_status VARCHAR(20) DEFAULT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BA388B751EE837B ON cart (user_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cart_item (guid UUID NOT NULL, cart_guid UUID DEFAULT NULL, item_guid UUID DEFAULT NULL, qty INT DEFAULT 1 NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE2527C48ACF8B ON cart_item (cart_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE2527E35F8B49 ON cart_item (item_guid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD CONSTRAINT FK_BA388B751EE837B FOREIGN KEY (user_guid) REFERENCES app_user (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527C48ACF8B FOREIGN KEY (cart_guid) REFERENCES cart (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527E35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_item_main_image_guid
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_item_brand_guid
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_item_rank
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_item_publish_state_rank_desc
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_item_publish_state_guid_rank
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_item_category_category_guid_item_guid
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP CONSTRAINT FK_BA388B751EE837B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP CONSTRAINT FK_F0FE2527C48ACF8B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP CONSTRAINT FK_F0FE2527E35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cart
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cart_item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_item_main_image_guid ON item (main_image_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_item_brand_guid ON item (brand_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_item_rank ON item (rank)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_item_publish_state_rank_desc ON item (publish_state, rank)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_item_publish_state_guid_rank ON item (publish_state, guid, rank)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_item_category_category_guid_item_guid ON item_category (category_guid, item_guid)
        SQL);
    }
}
