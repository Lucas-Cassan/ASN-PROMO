<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210322095832 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, picture VARCHAR(20) NOT NULL, number INT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, user1_id INT DEFAULT NULL, user2_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, created DATETIME NOT NULL, ended DATETIME DEFAULT NULL, INDEX IDX_232B318C56AE248B (user1_id), INDEX IDX_232B318C441B8B65 (user2_id), INDEX IDX_232B318C5DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE round (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, created DATETIME NOT NULL, ended DATETIME DEFAULT NULL, user1_board_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user2_board_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', board LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', removed_card INT NOT NULL, user1_action LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user2_action LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', set_number INT NOT NULL, user1_hand_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user2_hand_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', pioche LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_C5EEEA34E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL, birthday DATE NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C56AE248B FOREIGN KEY (user1_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C441B8B65 FOREIGN KEY (user2_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA34E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA34E48FD905');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C56AE248B');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C441B8B65');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C5DFCD4B8');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE round');
        $this->addSql('DROP TABLE `user`');
    }
}
