<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210918122117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_discount (id INT AUTO_INCREMENT NOT NULL, order_id_id INT DEFAULT NULL, discount_reason VARCHAR(255) NOT NULL, discount_amount DOUBLE PRECISION NOT NULL, total_discount DOUBLE PRECISION NOT NULL, discounted_total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_1856BFFCDAEAAA (order_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_discount ADD CONSTRAINT FK_1856BFFCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');

        $this->addSql('CREATE TABLE shopping_test.order_discount (id INT AUTO_INCREMENT NOT NULL, order_id_id INT DEFAULT NULL, discount_reason VARCHAR(255) NOT NULL, discount_amount DOUBLE PRECISION NOT NULL, total_discount DOUBLE PRECISION NOT NULL, discounted_total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_1856BFFCDAEAAA (order_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shopping_test.order_discount ADD CONSTRAINT FK_1856BFFCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_discount');
    }
}
