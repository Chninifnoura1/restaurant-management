<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261114202100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, sale_price NUMERIC(10, 2) NOT NULL, is_available TINYINT NOT NULL, category_id INT NOT NULL, INDEX IDX_957D8CB812469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dish_ingredient (id INT AUTO_INCREMENT NOT NULL, quantity NUMERIC(10, 3) NOT NULL, dish_id INT NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_77196056148EB0CB (dish_id), INDEX IDX_77196056933FE08C (ingredient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, unit VARCHAR(20) NOT NULL, stock_quantity NUMERIC(10, 3) NOT NULL, alert_threshold NUMERIC(10, 3) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE loss (id INT AUTO_INCREMENT NOT NULL, quantity NUMERIC(10, 3) NOT NULL, reason VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_DE211109933FE08C (ingredient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, restaurant_order_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_52EA1F09AD3AA3D7 (restaurant_order_id), INDEX IDX_52EA1F09148EB0CB (dish_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, supplier_name VARCHAR(150) NOT NULL, purchase_date DATETIME NOT NULL, reference VARCHAR(100) DEFAULT NULL, total_amount NUMERIC(10, 2) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE purchase_item (id INT AUTO_INCREMENT NOT NULL, quantity NUMERIC(10, 3) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, purchase_id INT NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_6FA8ED7D558FBEB9 (purchase_id), INDEX IDX_6FA8ED7D933FE08C (ingredient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE restaurant_order (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, served_at DATETIME DEFAULT NULL, table_number VARCHAR(20) DEFAULT NULL, stock_deducted_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_movement (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(30) NOT NULL, quantity NUMERIC(10, 3) NOT NULL, reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_BB1BC1B5933FE08C (ingredient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE dish_ingredient ADD CONSTRAINT FK_77196056148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE dish_ingredient ADD CONSTRAINT FK_77196056933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE loss ADD CONSTRAINT FK_DE211109933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09AD3AA3D7 FOREIGN KEY (restaurant_order_id) REFERENCES restaurant_order (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE purchase_item ADD CONSTRAINT FK_6FA8ED7D558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE purchase_item ADD CONSTRAINT FK_6FA8ED7D933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB812469DE2');
        $this->addSql('ALTER TABLE dish_ingredient DROP FOREIGN KEY FK_77196056148EB0CB');
        $this->addSql('ALTER TABLE dish_ingredient DROP FOREIGN KEY FK_77196056933FE08C');
        $this->addSql('ALTER TABLE loss DROP FOREIGN KEY FK_DE211109933FE08C');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09AD3AA3D7');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09148EB0CB');
        $this->addSql('ALTER TABLE purchase_item DROP FOREIGN KEY FK_6FA8ED7D558FBEB9');
        $this->addSql('ALTER TABLE purchase_item DROP FOREIGN KEY FK_6FA8ED7D933FE08C');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5933FE08C');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE dish_ingredient');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE loss');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE purchase_item');
        $this->addSql('DROP TABLE restaurant_order');
        $this->addSql('DROP TABLE stock_movement');
    }
}
