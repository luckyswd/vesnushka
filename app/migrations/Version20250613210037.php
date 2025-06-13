<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613210037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Важно: безопасно кастим к jsonb
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
    }

    public function down(Schema $schema): void
    {
        // Откатим обратно в JSON (если вдруг понадобится)
        $this->addSql(<<<'SQL'
            ALTER TABLE item
            ALTER COLUMN attributes TYPE json
            USING attributes::json
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE item
            ALTER COLUMN breadcrumbs TYPE json
            USING breadcrumbs::json
        SQL);
    }
}
