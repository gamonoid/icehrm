<?php

namespace KudosUser\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

/**
 * Admin-editable Kudos settings (Settings → Other, category "Kudos").
 *
 *   Kudos: Max Kudos Per Month   how many kudos one person may give per calendar
 *                                month; 2–10, default 5.
 *
 * Insert-if-missing, so an admin's chosen value survives a re-run.
 */
class KudosSettings extends AbstractMigration implements MigrationInterface
{
    const MAX_PER_MONTH = 'Kudos: Max Kudos Per Month';

    public function getName()
    {
        return 'kudos_settings';
    }

    public function up()
    {
        $rs = $this->db()->Execute('SELECT count(id) AS c FROM Settings WHERE name = ?', array(self::MAX_PER_MONTH));
        if ((int) $rs[0]['c'] > 0) {
            return true;
        }

        $options = array();
        for ($i = 2; $i <= 10; $i++) {
            $options[] = array((string) $i, (string) $i);
        }
        $meta = '["value", {"label":"Value","type":"select","source":' . json_encode($options) . '}]';

        $this->db()->Execute(
            'INSERT INTO Settings (name, value, description, meta, category) VALUES (?, ?, ?, ?, ?)',
            array(
                self::MAX_PER_MONTH,
                '5',
                'Maximum number of kudos each person can give per calendar month',
                $meta,
                'Kudos',
            )
        );

        return true;
    }

    public function down()
    {
        $this->db()->Execute('DELETE FROM Settings WHERE name = ?', array(self::MAX_PER_MONTH));
        return true;
    }
}
