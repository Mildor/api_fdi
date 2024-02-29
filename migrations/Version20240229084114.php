<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229084114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE basket_products (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, basket_id INT DEFAULT NULL, qte INT NOT NULL, INDEX IDX_D715558A4584665A (product_id), INDEX IDX_D715558A1BE1FB52 (basket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE basket_products ADD CONSTRAINT FK_D715558A4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE basket_products ADD CONSTRAINT FK_D715558A1BE1FB52 FOREIGN KEY (basket_id) REFERENCES basket (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket_products DROP FOREIGN KEY FK_D715558A4584665A');
        $this->addSql('ALTER TABLE basket_products DROP FOREIGN KEY FK_D715558A1BE1FB52');
        $this->addSql('DROP TABLE basket_products');
    }
}
