<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313141141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, mail LONGTEXT NOT NULL, date_entry DATETIME NOT NULL, is_active TINYINT NOT NULL, type_contract_id INT NOT NULL, INDEX IDX_5D9F75A16E6F376C (type_contract_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE employee_assignement (id INT AUTO_INCREMENT NOT NULL, employee_id INT NOT NULL, project_id INT NOT NULL, INDEX IDX_667472178C03F15C (employee_id), INDEX IDX_66747217166D1F9C (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, label LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE state (id INT AUTO_INCREMENT NOT NULL, label LONGTEXT NOT NULL, project_id INT NOT NULL, INDEX IDX_A393D2FB166D1F9C (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, label LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, date_deadline DATETIME DEFAULT NULL, state_id INT NOT NULL, employee_assignement_id INT DEFAULT NULL, INDEX IDX_527EDB255D83CC1 (state_id), INDEX IDX_527EDB253549DEF2 (employee_assignement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE type_contract (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A16E6F376C FOREIGN KEY (type_contract_id) REFERENCES type_contract (id)');
        $this->addSql('ALTER TABLE employee_assignement ADD CONSTRAINT FK_667472178C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE employee_assignement ADD CONSTRAINT FK_66747217166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE state ADD CONSTRAINT FK_A393D2FB166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB255D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB253549DEF2 FOREIGN KEY (employee_assignement_id) REFERENCES employee_assignement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A16E6F376C');
        $this->addSql('ALTER TABLE employee_assignement DROP FOREIGN KEY FK_667472178C03F15C');
        $this->addSql('ALTER TABLE employee_assignement DROP FOREIGN KEY FK_66747217166D1F9C');
        $this->addSql('ALTER TABLE state DROP FOREIGN KEY FK_A393D2FB166D1F9C');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB255D83CC1');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB253549DEF2');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP TABLE employee_assignement');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE state');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE type_contract');
    }
}
