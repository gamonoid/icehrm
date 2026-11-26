<?php

namespace KudosUser\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\DbDelta;
use Classes\Migration\MigrationInterface;
use Utils\LogManager;

class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return 'kudos_create_tables';
    }

    /**
     * DbDelta rules: column defaults carry no commas (it splits definitions on commas
     * without regard to quotes) and every index is declared inline (it only parses
     * CREATE TABLE). A failed CREATE never reaches getErrors(), so after this runs the
     * real check is SHOW TABLES.
     */
    private function getDDL()
    {
        return <<<'SQL'
create table KudosPosts
(
    id         bigint auto_increment primary key,
    sender     bigint       not null,
    recipient  bigint       not null,
    message    text         null,
    value_tag  varchar(100) null,
    audience   varchar(20)  default 'all' not null,
    image      varchar(500) null,
    status     varchar(20)  default 'Active' not null,
    created    datetime     null,
    key Ix_KudosPosts_Recipient (recipient, id),
    key Ix_KudosPosts_Sender (sender, id),
    key Ix_KudosPosts_Created (created)
);

create table KudosValues
(
    id          bigint auto_increment primary key,
    name        varchar(100) not null,
    description varchar(500) null,
    status      varchar(20)  default 'Active' not null,
    sort_order  int          default 0 not null,
    constraint Uq_KudosValues_Name unique (name)
);
SQL;
    }

    public function up()
    {
        $dbDelta = new DbDelta();
        $dbDelta->delta($this->getDDL(), true);

        $errors = $dbDelta->getErrors();
        if (!empty($errors)) {
            LogManager::getInstance()->error("Kudos migration errors: " . implode('; ', $errors));
            return false;
        }

        $this->seedValues();
        return true;
    }

    /**
     * The starter set of company values. Idempotent — skipped when the table already
     * has any row, so a customised list survives a re-run. Lives here rather than in
     * install() because install() is not called when the Modules row pre-exists.
     */
    private function seedValues()
    {
        $existing = $this->db()->Execute('SELECT id FROM KudosValues LIMIT 1');
        if (is_array($existing) && count($existing) > 0) {
            return;
        }
        $values = array(
            array('Teamwork', 'Went out of their way to help others succeed.'),
            array('Ownership', 'Took responsibility and saw it through.'),
            array('Customer Focus', 'Put the customer first.'),
            array('Innovation', 'Found a better way.'),
            array('Integrity', 'Did the right thing, even when it was hard.'),
        );
        $order = 0;
        foreach ($values as $value) {
            $order++;
            $this->db()->Execute(
                'INSERT INTO KudosValues (name, description, status, sort_order) VALUES (?, ?, ?, ?)',
                array($value[0], $value[1], 'Active', $order)
            );
        }
    }

    public function down()
    {
        return true;
    }
}
