<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112125529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredient_recette (ingredient_id INT NOT NULL, recette_id INT NOT NULL, INDEX IDX_488C6856933FE08C (ingredient_id), INDEX IDX_488C685689312FE9 (recette_id), PRIMARY KEY(ingredient_id, recette_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ingredient_recette ADD CONSTRAINT FK_488C6856933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_recette ADD CONSTRAINT FK_488C685689312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etapes ADD recette_id INT DEFAULT NULL, ADD description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE etapes ADD CONSTRAINT FK_E3443E1789312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id)');
        $this->addSql('CREATE INDEX IDX_E3443E1789312FE9 ON etapes (recette_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient_recette DROP FOREIGN KEY FK_488C6856933FE08C');
        $this->addSql('ALTER TABLE ingredient_recette DROP FOREIGN KEY FK_488C685689312FE9');
        $this->addSql('DROP TABLE ingredient_recette');
        $this->addSql('ALTER TABLE etapes DROP FOREIGN KEY FK_E3443E1789312FE9');
        $this->addSql('DROP INDEX IDX_E3443E1789312FE9 ON etapes');
        $this->addSql('ALTER TABLE etapes DROP recette_id, DROP description');
    }
}
