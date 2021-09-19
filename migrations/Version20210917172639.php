<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917172639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, order_id_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, unit_price DOUBLE PRECISION NOT NULL, total DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_52EA1F09FCDAEAAA (order_id_id), INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)');

        $this->addSql('CREATE TABLE shopping_test.order_item (id INT AUTO_INCREMENT NOT NULL, order_id_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, unit_price DOUBLE PRECISION NOT NULL, total DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_52EA1F09FCDAEAAA (order_id_id), INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shopping_test.order_item ADD CONSTRAINT FK_52EA1F09FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE shopping_test.order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)');


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

        $this->addSql('
        
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 2, 1, 10, 120.75, 1207.5, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 2, 2, 6, 49.5, 297, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 3, 1, 10, 120.75, 1207.5, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 3, 2, 6, 49.5, 297, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 3, 3, 2, 11.28, 22.56, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 4, 1, 10, 120.75, 1207.5, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 4, 2, 6, 49.5, 297, 1);
            INSERT INTO shopping_test.order_item (`id`, `order_id_id`, `product_id`, `quantity`, `unit_price`, `total`, `is_active`) VALUES (NULL, 4, 3, 2, 11.28, 22.56, 1);
        
        
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_item');
    }
}
