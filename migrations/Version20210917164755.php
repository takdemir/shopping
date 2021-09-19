<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917164755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, total DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        $this->addSql('CREATE TABLE shopping_test.order (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, total DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shopping_test.order ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        $this->addSql('INSERT INTO `order` (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (NULL, 1, 1504.5, "2021-09-18 06:59:11", 1)');
        $this->addSql('INSERT INTO `order` (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (NULL, 1, 1527.06, "2021-09-18 07:17:11", 1)');
        $this->addSql('INSERT INTO `order` (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (NULL, 1, 1527.06, "2021-09-18 07:39:11", 1)');

        $this->addSql('INSERT INTO shopping_test.order (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (NULL, 1, 1504.5, "2021-09-18 06:59:11", 1)');
        $this->addSql('INSERT INTO shopping_test.order (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (NULL, 1, 1527.06, "2021-09-18 07:17:11", 1)');
        $this->addSql('INSERT INTO shopping_test.order (`id`, `user_id`, `total`, `created_at`, `is_active`) VALUES (NULL, 1, 1527.06, "2021-09-18 07:39:11", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `order`');
    }
}
