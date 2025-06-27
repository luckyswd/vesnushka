<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627110211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_item_publish_state_rank_desc ON item (publish_state, rank)');
        $this->addSql('CREATE INDEX idx_item_rank ON item (rank)');
        $this->addSql('CREATE INDEX idx_item_brand_guid ON item (brand_guid)');
        $this->addSql('CREATE INDEX idx_item_main_image_guid ON item (main_image_guid)');
    }



    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
    }
}
