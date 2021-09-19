<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210918144811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discount (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, category_id INT DEFAULT NULL, product_id INT DEFAULT NULL, discount_code VARCHAR(50) NOT NULL, discount_class_name VARCHAR(50) NOT NULL, parameters JSON DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, start_at DATETIME DEFAULT NULL, expire_at DATETIME DEFAULT NULL, INDEX IDX_E1E0B40EA76ED395 (user_id), INDEX IDX_E1E0B40E12469DE2 (category_id), INDEX IDX_E1E0B40E4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discount ADD CONSTRAINT FK_E1E0B40EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discount ADD CONSTRAINT FK_E1E0B40E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE discount ADD CONSTRAINT FK_E1E0B40E4584665A FOREIGN KEY (product_id) REFERENCES product (id)');

        $this->addSql('CREATE TABLE shopping_test.discount (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, category_id INT DEFAULT NULL, product_id INT DEFAULT NULL, discount_code VARCHAR(50) NOT NULL, discount_class_name VARCHAR(50) NOT NULL, parameters JSON DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, start_at DATETIME DEFAULT NULL, expire_at DATETIME DEFAULT NULL, INDEX IDX_E1E0B40EA76ED395 (user_id), INDEX IDX_E1E0B40E12469DE2 (category_id), INDEX IDX_E1E0B40E4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shopping_test.discount ADD CONSTRAINT FK_E1E0B40EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shopping_test.discount ADD CONSTRAINT FK_E1E0B40E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE shopping_test.discount ADD CONSTRAINT FK_E1E0B40E4584665A FOREIGN KEY (product_id) REFERENCES product (id)');


        $this->addSql('
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, NULL, NULL, "N_PERCENT_OVER_K", "PercentOverDiscount", 1, "2021-09-18 16:36:19", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, 2, NULL, "BUY_N_GET_K", "BuyNPayKDiscount", 1, "2021-09-18 16:38:05", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
            INSERT INTO `discount` (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, 1, NULL, "BUY_N_DECREASE_PERCENT", "BuyNDecreasePercentDiscount", 1, "2021-09-18 16:39:09", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
        
        ');


        $this->addSql('
            INSERT INTO shopping_test.discount (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, NULL, NULL, "N_PERCENT_OVER_K", "PercentOverDiscount", 1, "2021-09-18 16:36:19", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
            INSERT INTO shopping_test.discount (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, 2, NULL, "BUY_N_GET_K", "BuyNPayKDiscount", 1, "2021-09-18 16:38:05", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
            INSERT INTO shopping_test.discount (`id`, `user_id`, `category_id`, `product_id`, `discount_code`, `discount_class_name`, `is_active`, `created_at`, `start_at`, `expire_at`, `parameters`) VALUES (NULL, NULL, 1, NULL, "BUY_N_DECREASE_PERCENT", "BuyNDecreasePercentDiscount", 1, "2021-09-18 16:39:09", "2021-09-18 16:36:19", "2021-10-30 16:36:19", NULL);
        
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE discount');
    }
}
