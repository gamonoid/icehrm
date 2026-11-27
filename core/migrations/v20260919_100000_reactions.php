<?php

namespace Classes\Migration;

/**
 * Generic emoji reactions, usable by any model: a row per (target, reactor, emoji).
 *
 * target_type names the model ('Kudos' first; 'DiscussionPost' can follow) and
 * target_id its row. employee is the reactor — the Employees id, or, for a user
 * with no profile (as admins may be), the negated Users id, the convention the rest
 * of the application already uses for that case (see Files.employee).
 *
 * The unique key is what makes "toggle" safe: a reactor holds at most one row per
 * emoji per target, so toggling is insert-or-delete with no counting.
 */
class v20260919_100000_reactions extends AbstractMigration
{
    public function up()
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `Reactions` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `target_type` VARCHAR(50) NOT NULL,
  `target_id` BIGINT NOT NULL,
  `employee` BIGINT NOT NULL,
  `emoji` VARCHAR(32) NOT NULL,
  `created` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Uq_Reactions_Target_Reactor_Emoji` (`target_type`, `target_id`, `employee`, `emoji`),
  KEY `Ix_Reactions_Target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;
        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
