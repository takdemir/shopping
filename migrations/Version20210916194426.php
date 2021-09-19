<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210916194426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE DATABASE shopping_test');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shopping_test.user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `user` (`id`, `email`, `roles`, `password`, `name`, `created_at`, `is_active`) VALUES (1, "taneryzb@hotmail.com", \'["ROLE_CUSTOMER", "ROLE_ADMIN"]\', "$2y$13$tnruAprPbR26w916Vc92tOonk1C0pt54TDDpgh4BVWAfzJN8te.N2", "Taner Akdemir", "2021-09-17 08:55:44", 1)');
        $this->addSql('INSERT INTO shopping_test.user (`id`, `email`, `roles`, `password`, `name`, `created_at`, `is_active`) VALUES (1, "taneryzb@hotmail.com", \'["ROLE_CUSTOMER", "ROLE_ADMIN"]\', "$2y$13$tnruAprPbR26w916Vc92tOonk1C0pt54TDDpgh4BVWAfzJN8te.N2", "Taner Akdemir", "2021-09-17 08:55:44", 1)');
        $this->addSql('INSERT INTO `user` (`id`, `email`, `roles`, `password`, `name`, `created_at`, `is_active`) VALUES (2, "metehan.kenan.akdemir@hotmail.com", \'["ROLE_CUSTOMER"]\', "$2y$13$tnruAprPbR26w916Vc92tOonk1C0pt54TDDpgh4BVWAfzJN8te.N2", "Metehan Kenan Akdemir", "2021-09-17 08:57:00", 1)');
        $this->addSql('INSERT INTO shopping_test.user (`id`, `email`, `roles`, `password`, `name`, `created_at`, `is_active`) VALUES (2, "metehan.kenan.akdemir@hotmail.com", \'["ROLE_CUSTOMER"]\', "$2y$13$tnruAprPbR26w916Vc92tOonk1C0pt54TDDpgh4BVWAfzJN8te.N2", "Metehan Kenan Akdemir", "2021-09-17 08:57:00", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
    }
}
