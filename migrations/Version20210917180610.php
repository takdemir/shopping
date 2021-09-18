<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917180610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('INSERT INTO `order` (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (2, 3, 1504.5, "2021-09-18 06:59:11", 1)');
        $this->addSql('INSERT INTO `order` (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (2, 3, 1527.06, "2021-09-18 07:17:11", 1)');
        $this->addSql('INSERT INTO `order` (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (2, 3, 1527.06, "2021-09-18 07:39:11", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP is_active');
    }
}
