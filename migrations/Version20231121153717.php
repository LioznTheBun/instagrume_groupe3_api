<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231121153717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, auteur_id INT NOT NULL, publication_id INT NOT NULL, contenu LONGTEXT NOT NULL, date_comm DATETIME NOT NULL, parentCommentId INT DEFAULT NULL, INDEX IDX_67F068BC60BB6FE6 (auteur_id), UNIQUE INDEX UNIQ_67F068BC556CA8E7 (parentCommentId), INDEX IDX_67F068BC38B217A7 (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publication (id INT AUTO_INCREMENT NOT NULL, auteur_id INT NOT NULL, photo VARCHAR(255) NOT NULL, date_publication DATETIME NOT NULL, description LONGTEXT NOT NULL, is_locked TINYINT(1) NOT NULL, INDEX IDX_AF3C677960BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating_commentaire (id INT AUTO_INCREMENT NOT NULL, commentaire_id INT NOT NULL, likes_count INT NOT NULL, dislikes_count INT NOT NULL, UNIQUE INDEX UNIQ_16A11B5BBA9CD190 (commentaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating_publication (id INT AUTO_INCREMENT NOT NULL, publication_id INT NOT NULL, likes_count INT NOT NULL, dislikes_count INT NOT NULL, UNIQUE INDEX UNIQ_DE6D149E38B217A7 (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, avatar VARCHAR(255) NOT NULL, pseudo VARCHAR(64) NOT NULL, is_banned TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC556CA8E7 FOREIGN KEY (parentCommentId) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C677960BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE rating_commentaire ADD CONSTRAINT FK_16A11B5BBA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE rating_publication ADD CONSTRAINT FK_DE6D149E38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC60BB6FE6');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC556CA8E7');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC38B217A7');
        $this->addSql('ALTER TABLE publication DROP FOREIGN KEY FK_AF3C677960BB6FE6');
        $this->addSql('ALTER TABLE rating_commentaire DROP FOREIGN KEY FK_16A11B5BBA9CD190');
        $this->addSql('ALTER TABLE rating_publication DROP FOREIGN KEY FK_DE6D149E38B217A7');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE publication');
        $this->addSql('DROP TABLE rating_commentaire');
        $this->addSql('DROP TABLE rating_publication');
        $this->addSql('DROP TABLE `user`');
    }
}
