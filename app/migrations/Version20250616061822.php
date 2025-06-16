<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616061822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE file (guid UUID NOT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, size INT NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
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
            ALTER TABLE item_images ADD CONSTRAINT FK_66E6CBA5E35F8B49 FOREIGN KEY (item_guid) REFERENCES item (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images ADD CONSTRAINT FK_66E6CBA5A293A7DC FOREIGN KEY (file_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE brand ADD image_guid UUID DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE brand ADD CONSTRAINT FK_1C52F958D955252C FOREIGN KEY (image_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C52F958D955252C ON brand (image_guid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD image_guid UUID DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1D955252C FOREIGN KEY (image_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64C19C1D955252C ON category (image_guid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD main_image_guid UUID DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD CONSTRAINT FK_1F1B251EE1AD514F FOREIGN KEY (main_image_guid) REFERENCES file (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251EE1AD514F ON item (main_image_guid)
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
            ALTER TABLE category DROP CONSTRAINT FK_64C19C1D955252C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP CONSTRAINT FK_1F1B251EE1AD514F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images DROP CONSTRAINT FK_66E6CBA5E35F8B49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_images DROP CONSTRAINT FK_66E6CBA5A293A7DC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_images
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_64C19C1D955252C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP image_guid
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1C52F958D955252C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE brand DROP image_guid
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1F1B251EE1AD514F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP main_image_guid
        SQL);
    }
}
