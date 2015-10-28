<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150702213736 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (
          id INT AUTO_INCREMENT NOT NULL,
          username VARCHAR(50) NOT NULL,
          email VARCHAR(50) NOT NULL,
          password VARCHAR(255) NOT NULL,
          timezone VARCHAR(255) NOT NULL,
          purchases_per_day INT UNSIGNED DEFAULT 0 NOT NULL,
          incomes_per_month INT UNSIGNED DEFAULT 0 NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB ROW_FORMAT=FIXED');
        $this->addSql('CREATE UNIQUE INDEX udx_user_1 ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX udx_user_2 ON user (email)');

        $this->addSql('
          CREATE TABLE email_confirmation (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            email CHAR(255) NOT NULL,
            confirmation_code CHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX udx_email_confirmation_1 (email),
            UNIQUE INDEX udx_email_confirmation_2 (user_id),
            PRIMARY KEY(id)
          ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('
          ALTER TABLE email_confirmation
          ADD CONSTRAINT fk_email_confirmation_1 FOREIGN KEY (user_id)
          REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('CREATE TABLE purchase (
          id INT AUTO_INCREMENT NOT NULL,
          user_id INT NOT NULL,
          title VARCHAR(255) NOT NULL,
          amount INT NOT NULL,
          price NUMERIC(10, 2) NOT NULL,
          bought_at DATE NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL, PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB ROW_FORMAT=FIXED');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT fk_purchase_1 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX idx_purchase_1 ON purchase (user_id)');

        $this->addSql('CREATE TABLE income (
          id INT AUTO_INCREMENT NOT NULL,
          user_id INT DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          price NUMERIC(10, 2) NOT NULL,
          date DATE NOT NULL, created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL, INDEX IDX_3FA862D0A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT fk_income_1 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE income');

        $this->addSql('DROP TABLE email_confirmation');
        $this->addSql('DROP TABLE user');
    }
}
