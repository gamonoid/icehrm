<?php

namespace DiscussionsAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

/**
 * The "Public" channel — the company feed that the Home page posts to and reads
 * from (see docs/HOME_AND_KUDOS_PLAN.md). Everyone may read, post and reply: it is
 * the shared wall, unlike Announcements where only admins post.
 *
 * is_system = 1 marks it built-in, which is what stops it being deleted — both in
 * the admin REST endpoint and in DiscussionChannel::executePreDeleteActions(), so
 * neither the UI nor the generic service.php path can remove it. Home depends on
 * this channel existing, so it must not be possible to delete it even as an admin.
 *
 * Idempotent: keyed on the name, so a re-run never duplicates it. Deliberately
 * separate from the Announcements seed in CreateTables, which keys on is_system and
 * would otherwise skip this one.
 */
class CreatePublicChannel extends AbstractMigration implements MigrationInterface
{
    const CHANNEL_NAME = 'Public';

    public function getName()
    {
        return 'discussions_create_public_channel';
    }

    public function up()
    {
        // Execute() returns the matching rows as an array (MyORM, not ADOdb).
        $existing = $this->db()->Execute(
            'SELECT id FROM DiscussionChannels WHERE name = ? LIMIT 1',
            array(self::CHANNEL_NAME)
        );
        if (is_array($existing) && count($existing) > 0) {
            // Already there (perhaps created by hand before this shipped): make sure
            // it is flagged built-in so it cannot be deleted.
            $this->db()->Execute(
                'UPDATE DiscussionChannels SET is_system = 1 WHERE name = ?',
                array(self::CHANNEL_NAME)
            );
            return true;
        }

        $now = date('Y-m-d H:i:s');
        $everyone = 'Admin,Manager,Employee';
        $this->db()->Execute(
            'INSERT INTO DiscussionChannels '
            . '(name, description, is_system, read_levels, post_levels, reply_levels, created, updated) '
            . 'VALUES (?, ?, 1, ?, ?, ?, ?, ?)',
            array(
                self::CHANNEL_NAME,
                'The company feed. Everyone can post, comment and react — this is what the Home page shows.',
                $everyone,
                $everyone,
                $everyone,
                $now,
                $now,
            )
        );

        return true;
    }

    public function down()
    {
        return true;
    }
}
