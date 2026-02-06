<?php
/**
 * Migration: Modernize Travel Module Fields
 *
 * This migration adds modern travel management fields to the EmployeeTravelRecords table
 * Phase 1: Critical improvements for better travel tracking and management
 *
 */

namespace Classes\Migration;

class v20251220_100000_modernize_travel_fields extends AbstractMigration
{
    public function up()
    {
        $sql = [];

        // Add trip type (more specific than Local/International)
        $sql[] = <<<'SQL'
ALTER TABLE EmployeeTravelRecords
ADD COLUMN `trip_type` enum('Domestic','International','Regional') NULL
AFTER `type`;
SQL;

        $sql[] = <<<'SQL'
ALTER TABLE EmployeeTravelRecords
ADD COLUMN `project_code` varchar(50) NULL;
SQL;

        $sql[] = <<<'SQL'
ALTER TABLE EmployeeTravelRecords
ADD COLUMN `confirmation_number` varchar(100) NULL;
SQL;

        $sql[] = <<<'SQL'
ALTER TABLE EmployeeTravelRecords
ADD COLUMN `airline_name` varchar(100) NULL;
SQL;

        $sql[] = <<<'SQL'
ALTER TABLE EmployeeTravelRecords
ADD COLUMN `flight_number` varchar(20) NULL;
SQL;

        // Migrate existing 'type' data to 'trip_type'
        // Assuming 'Local' → 'Domestic' and 'International' → 'International'
        $sql[] = <<<'SQL'
UPDATE EmployeeTravelRecords
SET trip_type = CASE
    WHEN type = 'Local' THEN 'Domestic'
    WHEN type = 'International' THEN 'International'
    ELSE 'Domestic'
END
WHERE trip_type IS NULL;
SQL;

        // Execute all queries
        $result = true;
        foreach ($sql as $query) {
            $result = $result && $this->executeQuery($query);
        }

        return $result;
    }

    public function down()
    {
        // For now, return true. In production, you might want to implement rollback
        return true;
    }
}
