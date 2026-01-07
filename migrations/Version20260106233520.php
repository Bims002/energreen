<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106233520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumption ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_2CFF2DF9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_2CFF2DF9A76ED395 ON consumption (user_id)');
        $this->addSql('ALTER TABLE lodgment ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE lodgment ADD CONSTRAINT FK_CA96599A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_CA96599A76ED395 ON lodgment (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumption DROP FOREIGN KEY FK_2CFF2DF9A76ED395');
        $this->addSql('DROP INDEX IDX_2CFF2DF9A76ED395 ON consumption');
        $this->addSql('ALTER TABLE consumption DROP user_id');
        $this->addSql('ALTER TABLE lodgment DROP FOREIGN KEY FK_CA96599A76ED395');
        $this->addSql('DROP INDEX IDX_CA96599A76ED395 ON lodgment');
        $this->addSql('ALTER TABLE lodgment DROP user_id');
    }
}
