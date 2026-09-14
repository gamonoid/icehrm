<?php

namespace Classes\Migration;

/**
 * Removes the two legacy "reports" module rows, whose directories are now deleted.
 *
 * Reporting is served entirely by the advance_reports extension. The legacy report
 * EXECUTION path went first (see v20260802_350005_remove_legacy_report_definitions,
 * which removed the seed report definitions after the Reports\{Admin,User}\Reports\*
 * classes were deleted); both module directories were empty shells after that —
 * core/admin/reports held only a meta.json, and core/modules/reports held a meta.json
 * plus the payslip Twig templates, which have moved to core/src/Reports/templates/
 * beside PayslipReport, the one class that still renders them.
 *
 * Both rows were already status='Disabled', so nothing changes functionally: this
 * clears the orphaned rows so the Modules table matches the filesystem. Keyed on
 * update_path (as v20251229_340014_remove_unused_modules does) rather than on name,
 * so an unrelated module that happens to be called "reports" is not touched.
 */
class v20260806_100000_remove_legacy_reports_modules extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
DELETE FROM `Modules` WHERE `update_path` IN (
    'admin>reports',
    'modules>reports'
);
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        // Not reversible: the module directories the rows point at no longer exist,
        // so a re-inserted row would reference a module that cannot be loaded.
        return true;
    }
}
