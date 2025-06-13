<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613080036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT fk_6a41d10a126f525e
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT fk_6a41d10a12469de2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_6a41d10a12469de2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_6a41d10a126f525e
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT item_category_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD item_guid UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD category_guid UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP item_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP category_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10AE35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10AA0F4B5F5 FOREIGN KEY (category_guid) REFERENCES category (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41D10AE35F8B49 ON item_category (item_guid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41D10AA0F4B5F5 ON item_category (category_guid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD PRIMARY KEY (item_guid, category_guid)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10AE35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP CONSTRAINT FK_6A41D10AA0F4B5F5
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6A41D10AE35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6A41D10AA0F4B5F5
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX item_category_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD item_id UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD category_id UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP item_guid
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category DROP category_guid
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT fk_6a41d10a126f525e FOREIGN KEY (item_id) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD CONSTRAINT fk_6a41d10a12469de2 FOREIGN KEY (category_id) REFERENCES category (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_6a41d10a12469de2 ON item_category (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_6a41d10a126f525e ON item_category (item_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_category ADD PRIMARY KEY (item_id, category_id)
        SQL);
    }
}
