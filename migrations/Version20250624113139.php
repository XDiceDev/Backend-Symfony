<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624113139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE amenity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE client_contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cottage_amenity (cottage_id INT NOT NULL, amenity_id INT NOT NULL, INDEX IDX_D476E5617FF9E93 (cottage_id), INDEX IDX_D476E569F9F1305 (amenity_id), PRIMARY KEY(cottage_id, amenity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cottage_amenity ADD CONSTRAINT FK_D476E5617FF9E93 FOREIGN KEY (cottage_id) REFERENCES cottage (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cottage_amenity ADD CONSTRAINT FK_D476E569F9F1305 FOREIGN KEY (amenity_id) REFERENCES amenity (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cottage DROP amenities
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cottage_amenity DROP FOREIGN KEY FK_D476E5617FF9E93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cottage_amenity DROP FOREIGN KEY FK_D476E569F9F1305
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE amenity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client_contact
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cottage_amenity
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cottage ADD amenities VARCHAR(255) NOT NULL
        SQL);
    }
}
