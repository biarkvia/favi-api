<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612085046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for partner orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE partner_order (id INT AUTO_INCREMENT NOT NULL, partner_id VARCHAR(255) NOT NULL, order_id VARCHAR(255) NOT NULL, expected_delivery_date DATE NOT NULL, total_value NUMERIC(12, 2) NOT NULL, raw_payload JSON NOT NULL, UNIQUE INDEX partner_order_unique (partner_id, order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE partner_order_item (id INT AUTO_INCREMENT NOT NULL, product_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(12, 2) NOT NULL, quantity INT NOT NULL, partner_order_id INT NOT NULL, INDEX IDX_481B1CEFEE5EEB82 (partner_order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE partner_order_item ADD CONSTRAINT FK_481B1CEFEE5EEB82 FOREIGN KEY (partner_order_id) REFERENCES partner_order (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE partner_order_item DROP FOREIGN KEY FK_481B1CEFEE5EEB82');
        $this->addSql('DROP TABLE partner_order');
        $this->addSql('DROP TABLE partner_order_item');
    }
}
