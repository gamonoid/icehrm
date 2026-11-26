<?php

namespace DiscussionsAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\DbDelta;
use Classes\Migration\MigrationInterface;
use Utils\LogManager;

class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return 'discussions_create_tables';
    }

    /**
     * Column defaults deliberately carry no commas: DbDelta splits column definitions
     * on commas without regard to quotes, so a default like 'Admin,Manager' would be
     * cut into invalid SQL — silently, since the failure never reaches getErrors().
     * The level lists are always written by the code that creates a channel. Indexes
     * are declared inline for the same reason: DbDelta only parses CREATE TABLE.
     */
    private function getDDL()
    {
        return <<<'SQL'
create table DiscussionChannels
(
    id           bigint auto_increment primary key,
    name         varchar(100) not null,
    description  text         null,
    is_system    tinyint(1)   default 0 not null,
    read_levels  varchar(100) not null,
    post_levels  varchar(100) not null,
    reply_levels varchar(100) not null,
    created_by   bigint       null,
    created      datetime     null,
    updated      datetime     null,
    constraint Uq_DiscussionChannels_Name unique (name)
);

create table DiscussionPosts
(
    id              bigint auto_increment primary key,
    channel         bigint     not null,
    author_user     bigint     not null,
    author_employee bigint     null,
    content         mediumtext null,
    attachments     text       null,
    created         datetime   null,
    updated         datetime   null,
    key Ix_DiscussionPosts_Channel (channel, id),
    constraint Fk_DiscussionPosts_Channel
        foreign key (channel) references DiscussionChannels (id)
            on update cascade on delete cascade
);

create table DiscussionReplies
(
    id              bigint auto_increment primary key,
    post            bigint   not null,
    parent          bigint   null,
    author_user     bigint   not null,
    author_employee bigint   null,
    body            text     null,
    created         datetime null,
    key Ix_DiscussionReplies_Post (post, id),
    constraint Fk_DiscussionReplies_Post
        foreign key (post) references DiscussionPosts (id)
            on update cascade on delete cascade,
    constraint Fk_DiscussionReplies_Parent
        foreign key (parent) references DiscussionReplies (id)
            on update cascade on delete cascade
);
SQL;
    }

    public function up()
    {
        $dbDelta = new DbDelta();
        $dbDelta->delta($this->getDDL(), true);

        $errors = $dbDelta->getErrors();
        if (!empty($errors)) {
            LogManager::getInstance()->error("Discussions migration errors: " . implode('; ', $errors));
            return false;
        }

        $this->seedAnnouncements();
        return true;
    }

    /**
     * The built-in channel every installation starts with: readable by everyone, only
     * admins may post, everyone may reply. Idempotent — keyed on is_system, so a
     * renamed Announcements channel is not duplicated on a re-run.
     */
    private function seedAnnouncements()
    {
        // Execute() returns the matching rows as an array (MyORM, not ADOdb).
        $existing = $this->db()->Execute('SELECT id FROM DiscussionChannels WHERE is_system = 1 LIMIT 1');
        if (is_array($existing) && count($existing) > 0) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->db()->Execute(
            'INSERT INTO DiscussionChannels '
            . '(name, description, is_system, read_levels, post_levels, reply_levels, created, updated) '
            . 'VALUES (?, ?, 1, ?, ?, ?, ?, ?)',
            array(
                'Announcements',
                'Company-wide announcements. Everyone can read and reply; only administrators can post.',
                'Admin,Manager,Employee',
                'Admin',
                'Admin,Manager,Employee',
                $now,
                $now,
            )
        );
    }

    public function down()
    {
        return true;
    }
}
