<?php
namespace Classes\Migration;

class v20260707_350004_add_overtime_categories extends AbstractMigration {

    public function up(){
        // Seed a realistic default set of overtime categories so the Overtime
        // module is usable out of the box. Idempotent: only inserts names that
        // don't exist yet (fresh installs and existing instances alike).
        $categories = [
            'Weekday Overtime (1.5x)',
            'Weekend Overtime (2x)',
            'Public Holiday Overtime (2.5x)',
            'Night Shift Overtime',
            'On-Call / Emergency Overtime',
        ];

        foreach ($categories as $name) {
            $existing = $this->executeQuery(
                "SELECT id FROM OvertimeCategories WHERE name = '" . addslashes($name) . "'"
            );
            if (empty($existing)) {
                $this->executeQuery(
                    "INSERT INTO OvertimeCategories (name, created, updated) VALUES ('"
                    . addslashes($name) . "', NULL, NULL)"
                );
            }
        }

        return true;
    }

    public function down(){
        // Seeded reference data — leave in place on rollback for safety.
        return true;
    }

}
