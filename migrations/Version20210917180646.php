<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917180646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('
        
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 2, 1, 10, 120.75, 1207.5, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 2, 2, 6, 49.5, 297, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 3, 1, 10, 120.75, 1207.5, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 3, 2, 6, 49.5, 297, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 3, 3, 2, 11.28, 22.56, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 4, 1, 10, 120.75, 1207.5, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 4, 2, 6, 49.5, 297, 1);
            INSERT INTO `order_item` (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 4, 3, 2, 11.28, 22.56, 1);
        
        
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP is_active');
    }
}
