<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118143750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE archive_bilan_carbone (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', score_total DOUBLE PRECISION NOT NULL, details JSON DEFAULT NULL, INDEX IDX_57C7444EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE archive_consumption (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total_kwh DOUBLE PRECISION NOT NULL, estimated_price DOUBLE PRECISION NOT NULL, archived_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FC5DA8D8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lodgment_appliances (lodgment_id INT NOT NULL, appliance_id INT NOT NULL, INDEX IDX_EF7AE8CB2303C2ED (lodgment_id), INDEX IDX_EF7AE8CBE1EFC7B6 (appliance_id), PRIMARY KEY(lodgment_id, appliance_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archive_bilan_carbone ADD CONSTRAINT FK_57C7444EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE archive_consumption ADD CONSTRAINT FK_FC5DA8D8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lodgment_appliances ADD CONSTRAINT FK_EF7AE8CB2303C2ED FOREIGN KEY (lodgment_id) REFERENCES lodgment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodgment_appliances ADD CONSTRAINT FK_EF7AE8CBE1EFC7B6 FOREIGN KEY (appliance_id) REFERENCES appliance (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumption ADD total_kwh DOUBLE PRECISION DEFAULT NULL, ADD estimated_price DOUBLE PRECISION DEFAULT NULL, CHANGE past_consumption past_consumption DOUBLE PRECISION NOT NULL, CHANGE billing_date billing_date DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archive_bilan_carbone DROP FOREIGN KEY FK_57C7444EA76ED395');
        $this->addSql('ALTER TABLE archive_consumption DROP FOREIGN KEY FK_FC5DA8D8A76ED395');
        $this->addSql('ALTER TABLE lodgment_appliances DROP FOREIGN KEY FK_EF7AE8CB2303C2ED');
        $this->addSql('ALTER TABLE lodgment_appliances DROP FOREIGN KEY FK_EF7AE8CBE1EFC7B6');
        $this->addSql('DROP TABLE archive_bilan_carbone');
        $this->addSql('DROP TABLE archive_consumption');
        $this->addSql('DROP TABLE lodgment_appliances');
        $this->addSql('ALTER TABLE consumption DROP total_kwh, DROP estimated_price, CHANGE past_consumption past_consumption INT NOT NULL, CHANGE billing_date billing_date DATE NOT NULL');
    }
}
