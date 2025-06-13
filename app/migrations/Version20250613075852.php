<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613075852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE brand (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, items VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1C52F958F47645AE ON brand (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (guid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(guid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD brand_guid UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD CONSTRAINT FK_1F1B251EC08C50D FOREIGN KEY (brand_guid) REFERENCES brand (guid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251EC08C50D ON item (brand_guid)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP CONSTRAINT FK_1F1B251EC08C50D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE brand
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1F1B251EC08C50D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP brand_guid
        SQL);
    }
}
