<?php
/**
 * Migration: Add Travel Projects Table
 *
 * This migration creates a new TravelProjects table to manage travel projects.
 * Travel projects are used to categorize and track travel requests by project.
 * The project_code field in EmployeeTravelRecords references the code field in this table.
 *
 * Fields:
 * - id: Primary key
 * - code: Unique project code (e.g., "PROJ001", "GENERAL")
 * - name: Project name
 * - description: Project details
 * - status: Active/Inactive status
 * - budget: Optional project budget
 * - currency: Optional currency for budget
 * - start_date: Optional project start date
 * - end_date: Optional project end date
 * - created: Record creation timestamp
 * - updated: Record last update timestamp
 */

namespace Classes\Migration;

class v20251220_100100_add_travel_projects_table extends AbstractMigration
{
    public function up()
    {
        $sql = [];

        // Create TravelProjects table
        $sql[] = <<<'SQL'
CREATE TABLE IF NOT EXISTS `TravelProjects` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL,
    `name` varchar(200) NOT NULL,
    `description` text NULL,
    `status` enum('Active','Inactive') DEFAULT 'Active',
    `budget` decimal(15,2) NULL,
    `currency` bigint(20) NULL,
    `start_date` date NULL,
    `end_date` date NULL,
    `created` datetime NULL DEFAULT NULL,
    `updated` datetime NULL DEFAULT NULL,
    UNIQUE KEY `code` (`code`),
    KEY `status` (`status`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
SQL;

        // Insert default "General" project
        $sql[] = <<<'SQL'
INSERT INTO `TravelProjects`
(`code`, `name`, `description`, `status`, `created`, `updated`)
VALUES
('GENERAL', 'General', 'General travel project for miscellaneous travel requests', 'Active', NOW(), NOW());
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
        // For rollback: drop the table
        return true;
    }
}
