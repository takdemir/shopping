<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210918163358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discount CHANGE start_at start_at DATETIME DEFAULT NULL, CHANGE expire_at expire_at DATETIME DEFAULT NULL');
        $this->addSql('
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`) VALUES (NULL, NULL, NULL, NULL, "10_PERCENT_OVER_1000", "PercentOverDiscount", 1, "2021-09-18 16:36:19", NULL, NULL);
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`) VALUES (NULL, NULL, 2, NULL, "BUY_5_GET_1", "BuyNPayKDiscount", 1, "2021-09-18 16:38:05", NULL, NULL);
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`) VALUES (NULL, NULL, 1, NULL, "BUY_N_DECREASE_PERCENT", "BuyNDecreasePercentDiscount", 1, "2021-09-18 16:39:09", NULL, NULL);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discount CHANGE start_at start_at DATETIME DEFAULT NULL, CHANGE expire_at expire_at DATETIME DEFAULT NULL');
    }
}
