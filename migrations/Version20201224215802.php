<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201224215802 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE adresses (id INT AUTO_INCREMENT NOT NULL, nom_rue VARCHAR(255) NOT NULL, num_rue SMALLINT DEFAULT NULL, code_postal SMALLINT NOT NULL, ville VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cadeaux (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, age SMALLINT NOT NULL, prix SMALLINT NOT NULL, INDEX IDX_C15356EFBCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personnes (id INT AUTO_INCREMENT NOT NULL, adresse_id INT NOT NULL, nom VARCHAR(255) NOT NULL, sexe VARCHAR(255) NOT NULL, INDEX IDX_2BB4FE2B4DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personnes_cadeaux (personnes_id INT NOT NULL, cadeaux_id INT NOT NULL, INDEX IDX_120CC760146AD7BC (personnes_id), INDEX IDX_120CC760DA7CA8F0 (cadeaux_id), PRIMARY KEY(personnes_id, cadeaux_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cadeaux ADD CONSTRAINT FK_C15356EFBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE personnes ADD CONSTRAINT FK_2BB4FE2B4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresses (id)');
        $this->addSql('ALTER TABLE personnes_cadeaux ADD CONSTRAINT FK_120CC760146AD7BC FOREIGN KEY (personnes_id) REFERENCES personnes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE personnes_cadeaux ADD CONSTRAINT FK_120CC760DA7CA8F0 FOREIGN KEY (cadeaux_id) REFERENCES cadeaux (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personnes DROP FOREIGN KEY FK_2BB4FE2B4DE7DC5C');
        $this->addSql('ALTER TABLE personnes_cadeaux DROP FOREIGN KEY FK_120CC760DA7CA8F0');
        $this->addSql('ALTER TABLE cadeaux DROP FOREIGN KEY FK_C15356EFBCF5E72D');
        $this->addSql('ALTER TABLE personnes_cadeaux DROP FOREIGN KEY FK_120CC760146AD7BC');
        $this->addSql('DROP TABLE adresses');
        $this->addSql('DROP TABLE cadeaux');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE personnes');
        $this->addSql('DROP TABLE personnes_cadeaux');
    }
}
