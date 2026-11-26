<?php

namespace InboxUser\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\DbDelta;
use Classes\Migration\MigrationInterface;
use Utils\LogManager;

class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return 'inbox_create_tables';
    }

    /**
     * DbDelta rules (see the Discussions migration): no commas inside quoted
     * defaults, indexes declared inline, only CREATE TABLE is parsed.
     *
     * A conversation is one row per pair, with participant_a < participant_b so the
     * pair has exactly one row. Each side's state — whether they have hidden it from
     * their list, and the last message they have read — lives on the same row.
     */
    private function getDDL()
    {
        return <<<'SQL'
create table InboxConversations
(
    id              bigint auto_increment primary key,
    participant_a   bigint     not null,
    participant_b   bigint     not null,
    hidden_a        tinyint(1) default 0 not null,
    hidden_b        tinyint(1) default 0 not null,
    last_read_a     bigint     default 0 not null,
    last_read_b     bigint     default 0 not null,
    last_message_id bigint     default 0 not null,
    last_message_at datetime   null,
    created         datetime   null,
    constraint Uq_InboxConversations_Pair unique (participant_a, participant_b),
    key Ix_InboxConversations_A (participant_a, last_message_id),
    key Ix_InboxConversations_B (participant_b, last_message_id),
    constraint Fk_InboxConversations_A
        foreign key (participant_a) references Employees (id)
            on update cascade on delete cascade,
    constraint Fk_InboxConversations_B
        foreign key (participant_b) references Employees (id)
            on update cascade on delete cascade
);

create table InboxMessages
(
    id           bigint auto_increment primary key,
    conversation bigint   not null,
    sender       bigint   not null,
    body         text     null,
    attachments  text     null,
    created      datetime null,
    updated      datetime null,
    key Ix_InboxMessages_Conversation (conversation, id),
    constraint Fk_InboxMessages_Conversation
        foreign key (conversation) references InboxConversations (id)
            on update cascade on delete cascade,
    constraint Fk_InboxMessages_Sender
        foreign key (sender) references Employees (id)
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
            LogManager::getInstance()->error("Inbox migration errors: " . implode('; ', $errors));
            return false;
        }
        return true;
    }

    public function down()
    {
        return true;
    }
}
