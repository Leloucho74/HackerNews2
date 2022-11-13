<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221110185804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_user (post_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(post_id, user_id), CONSTRAINT FK_44C6B1424B89032C FOREIGN KEY (post_id) REFERENCES symfony_demo_post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_44C6B142A76ED395 FOREIGN KEY (user_id) REFERENCES symfony_demo_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_44C6B1424B89032C ON post_user (post_id)');
        $this->addSql('CREATE INDEX IDX_44C6B142A76ED395 ON post_user (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__symfony_demo_post AS SELECT id, author_id, title, slug, summary, content, published_at, link, type, user_id_votes FROM symfony_demo_post');
        $this->addSql('DROP TABLE symfony_demo_post');
        $this->addSql('CREATE TABLE symfony_demo_post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, summary VARCHAR(255) DEFAULT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, link VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, user_id_votes CLOB NOT NULL --(DC2Type:json)
        , number_of_votes INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_58A92E65F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO symfony_demo_post (id, author_id, title, slug, summary, content, published_at, link, type, user_id_votes) SELECT id, author_id, title, slug, summary, content, published_at, link, type, user_id_votes FROM __temp__symfony_demo_post');
        $this->addSql('DROP TABLE __temp__symfony_demo_post');
        $this->addSql('CREATE INDEX IDX_58A92E65F675F31B ON symfony_demo_post (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_user');
        $this->addSql('CREATE TEMPORARY TABLE __temp__symfony_demo_post AS SELECT id, author_id, title, slug, summary, content, published_at, link, type, user_id_votes FROM symfony_demo_post');
        $this->addSql('DROP TABLE symfony_demo_post');
        $this->addSql('CREATE TABLE symfony_demo_post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, summary VARCHAR(255) NOT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, link VARCHAR(255) DEFAULT NULL, type VARCHAR(256) DEFAULT \'"ask"\' NOT NULL, user_id_votes VARCHAR(255) DEFAULT \'"[]"\' NOT NULL, CONSTRAINT FK_58A92E65F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO symfony_demo_post (id, author_id, title, slug, summary, content, published_at, link, type, user_id_votes) SELECT id, author_id, title, slug, summary, content, published_at, link, type, user_id_votes FROM __temp__symfony_demo_post');
        $this->addSql('DROP TABLE __temp__symfony_demo_post');
        $this->addSql('CREATE INDEX IDX_58A92E65F675F31B ON symfony_demo_post (author_id)');
    }
}
