<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120181630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appliance (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, power INT NOT NULL, usage_appliance DOUBLE PRECISION NOT NULL, mode VARCHAR(255) NOT NULL, duration DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE archive_bilan_carbone (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', score_total DOUBLE PRECISION NOT NULL, details JSON DEFAULT NULL, INDEX IDX_57C7444EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE archive_consumption (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total_kwh DOUBLE PRECISION NOT NULL, estimated_price DOUBLE PRECISION NOT NULL, archived_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FC5DA8D8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bilan_carbone (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, logement DOUBLE PRECISION NOT NULL, numerique DOUBLE PRECISION NOT NULL, electromenager DOUBLE PRECISION NOT NULL, alimentation DOUBLE PRECISION NOT NULL, transports DOUBLE PRECISION NOT NULL, textile DOUBLE PRECISION NOT NULL, total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_468AF6A0FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumption (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, past_consumption DOUBLE PRECISION NOT NULL, billing_date DATETIME NOT NULL, total_kwh DOUBLE PRECISION DEFAULT NULL, estimated_price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2CFF2DF9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lodgment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, lodgment_type VARCHAR(255) NOT NULL, surface INT NOT NULL, occupant INT NOT NULL, INDEX IDX_CA96599A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lodgment_appliances (lodgment_id INT NOT NULL, appliance_id INT NOT NULL, INDEX IDX_EF7AE8CB2303C2ED (lodgment_id), INDEX IDX_EF7AE8CBE1EFC7B6 (appliance_id), PRIMARY KEY(lodgment_id, appliance_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, statut_pro VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archive_bilan_carbone ADD CONSTRAINT FK_57C7444EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE archive_consumption ADD CONSTRAINT FK_FC5DA8D8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE bilan_carbone ADD CONSTRAINT FK_468AF6A0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_2CFF2DF9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lodgment ADD CONSTRAINT FK_CA96599A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lodgment_appliances ADD CONSTRAINT FK_EF7AE8CB2303C2ED FOREIGN KEY (lodgment_id) REFERENCES lodgment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodgment_appliances ADD CONSTRAINT FK_EF7AE8CBE1EFC7B6 FOREIGN KEY (appliance_id) REFERENCES appliance (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archive_bilan_carbone DROP FOREIGN KEY FK_57C7444EA76ED395');
        $this->addSql('ALTER TABLE archive_consumption DROP FOREIGN KEY FK_FC5DA8D8A76ED395');
        $this->addSql('ALTER TABLE bilan_carbone DROP FOREIGN KEY FK_468AF6A0FB88E14F');
        $this->addSql('ALTER TABLE consumption DROP FOREIGN KEY FK_2CFF2DF9A76ED395');
        $this->addSql('ALTER TABLE lodgment DROP FOREIGN KEY FK_CA96599A76ED395');
        $this->addSql('ALTER TABLE lodgment_appliances DROP FOREIGN KEY FK_EF7AE8CB2303C2ED');
        $this->addSql('ALTER TABLE lodgment_appliances DROP FOREIGN KEY FK_EF7AE8CBE1EFC7B6');
        $this->addSql('DROP TABLE appliance');
        $this->addSql('DROP TABLE archive_bilan_carbone');
        $this->addSql('DROP TABLE archive_consumption');
        $this->addSql('DROP TABLE bilan_carbone');
        $this->addSql('DROP TABLE consumption');
        $this->addSql('DROP TABLE lodgment');
        $this->addSql('DROP TABLE lodgment_appliances');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
