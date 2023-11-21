<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231121134429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD parentCommentId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC556CA8E7 FOREIGN KEY (parentCommentId) REFERENCES commentaire (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67F068BC556CA8E7 ON commentaire (parentCommentId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC556CA8E7');
        $this->addSql('DROP INDEX UNIQ_67F068BC556CA8E7 ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP parentCommentId');
    }
}
