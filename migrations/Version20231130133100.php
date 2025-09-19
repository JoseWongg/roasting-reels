<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231130133100 extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Remove ROLE_USER from roles array for each user';
    }

    public function up(Schema $schema): void
    {
         // Remove 'ROLE_USER' from the roles array for each user
        $this->addSql("UPDATE user SET roles = REPLACE(roles, '\"ROLE_USER\"', '') WHERE JSON_CONTAINS(roles, '\"ROLE_USER\"')");
    }

    public function down(Schema $schema): void
    {
        // Add 'ROLE_USER' back to the roles array for each user
        $this->addSql("UPDATE user SET roles = JSON_ARRAY_APPEND(roles, '$', '\"ROLE_USER\"') WHERE NOT JSON_CONTAINS(roles, '\"ROLE_USER\"')");
    }
}