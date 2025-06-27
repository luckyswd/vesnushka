<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627071445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE item
            ALTER COLUMN attributes TYPE jsonb
            USING attributes::jsonb
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE item
            ALTER COLUMN breadcrumbs TYPE jsonb
            USING breadcrumbs::jsonb
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE item
            ALTER COLUMN price TYPE jsonb
            USING price::jsonb
        SQL);
    }

    public function down(Schema $schema): void
    {

    }
}
