<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917090728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('INSERT INTO `user` (`id`, `email`, `roles`, `password`, `name`, `created_at`, `is_active`) VALUES (NULL, "taneryzb@hotmail.com", "[\'ROLE_CUSTOMER\', \'ROLE_ADMIN\']", "$2y$13$tnruAprPbR26w916Vc92tOonk1C0pt54TDDpgh4BVWAfzJN8te.N2", "Taner Akdemir", "2021-09-17 08:55:44", 1)');
        $this->addSql('INSERT INTO `user` (`id`, `email`, `roles`, `password`, `name`, `created_at`, `is_active`) VALUES (NULL, "metehan.kenan.akdemir@hotmail.com", "[\'ROLE_CUSTOMER\']", "$2y$13$tnruAprPbR26w916Vc92tOonk1C0pt54TDDpgh4BVWAfzJN8te.N2", "Metehan Kenan Akdemir", "2021-09-17 08:57:00", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP is_active');
    }
}
