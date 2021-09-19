<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917150816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, price NUMERIC(8, 2) NOT NULL, description LONGTEXT DEFAULT NULL, currency VARCHAR(5) NOT NULL, stock INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE TABLE shopping_test.product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, price NUMERIC(8, 2) NOT NULL, description LONGTEXT DEFAULT NULL, currency VARCHAR(5) NOT NULL, stock INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shopping_test.product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');

        $this->addSql('INSERT INTO `product` (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 1, "Black&Decker A7062 40 Parça Cırcırlı Tornavida Seti", 1, 120.75, NULL, "TL", 10, "2021-09-17 15:38:48")');
        $this->addSql('INSERT INTO `product` (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 1, "Reko Mini Tamir Hassas Tornavida Seti 32\'li", 1, 49.5, NULL, "TL", 10, "2021-09-17 15:39:15")');
        $this->addSql('INSERT INTO `product` (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 2, "Viko Karre Anahtar - Beyaz", 1, 11.28, NULL, "TL", 10, "2021-09-17 15:39:45")');
        $this->addSql('INSERT INTO `product` (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 2, "Legrand Salbei Anahtar, Alüminyum", 1, 22.8, NULL, "TL", 10, "2021-09-17 15:40:11")');
        $this->addSql('INSERT INTO `product` (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 2, "Schneider Asfora Beyaz Komütatör", 1, 12.95, NULL, "TL", 10, "2021-09-17 15:40:33")');

        $this->addSql('INSERT INTO shopping_test.product (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 1, "Black&Decker A7062 40 Parça Cırcırlı Tornavida Seti", 1, 120.75, NULL, "TL", 10, "2021-09-17 15:38:48")');
        $this->addSql('INSERT INTO shopping_test.product (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 1, "Reko Mini Tamir Hassas Tornavida Seti 32\'li", 1, 49.5, NULL, "TL", 10, "2021-09-17 15:39:15")');
        $this->addSql('INSERT INTO shopping_test.product (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 2, "Viko Karre Anahtar - Beyaz", 1, 11.28, NULL, "TL", 10, "2021-09-17 15:39:45")');
        $this->addSql('INSERT INTO shopping_test.product (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 2, "Legrand Salbei Anahtar, Alüminyum", 1, 22.8, NULL, "TL", 10, "2021-09-17 15:40:11")');
        $this->addSql('INSERT INTO shopping_test.product (`id`, `category_id`, `name`, `is_active`, `price`, `description`, `currency`, `stock`, `created_at`) VALUES (NULL, 2, "Schneider Asfora Beyaz Komütatör", 1, 12.95, NULL, "TL", 10, "2021-09-17 15:40:33")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product');
    }
}
