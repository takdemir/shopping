<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210919084059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discount CHANGE start_at start_at DATETIME NOT NULL, CHANGE expire_at expire_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE order_discount DROP FOREIGN KEY FK_1856BFE415FB15');
        $this->addSql('DROP INDEX IDX_1856BFE415FB15 ON order_discount');
        $this->addSql('ALTER TABLE order_discount DROP order_item_id');
        $this->addSql('
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, NULL, NULL, "N_PERCENT_OVER_K", "PercentOverDiscount", 1, "2021-09-18 16:36:19", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, 2, NULL, "BUY_N_GET_K", "BuyNPayKDiscount", 1, "2021-09-18 16:38:05", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, 1, NULL, "BUY_N_DECREASE_PERCENT", "BuyNDecreasePercentDiscount", 1, "2021-09-18 16:39:09", "2021-09-18 16:36:19", "2021-10-30 16:36:19", "a:0:{}");
        
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discount CHANGE start_at start_at DATETIME DEFAULT NULL, CHANGE expire_at expire_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE order_discount ADD order_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_discount ADD CONSTRAINT FK_1856BFE415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id)');
        $this->addSql('CREATE INDEX IDX_1856BFE415FB15 ON order_discount (order_item_id)');
    }
}
