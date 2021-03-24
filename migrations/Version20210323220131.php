<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210323220131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AAA76ED395');
        $this->addSql('DROP INDEX UNIQ_574767AAA76ED395 ON stats');
        $this->addSql('ALTER TABLE stats DROP user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stats ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_574767AAA76ED395 ON stats (user_id)');
    }
}
