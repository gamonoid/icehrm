<?php
namespace Classes\Migration;

use FieldNames\Common\Model\FieldNameMapping;
use Model\Setting;

/**
 * Employee fields gap plan (docs/EMPLOYEE_FIELDS_PLAN.md), phases A–C.3:
 *
 * - New employment, offboarding and personal columns on Employees.
 * - Lookups: WorkLocations, CostCenters, TerminationReasons (seeded).
 * - EmployeeBankAccounts (account numbers are encrypted by the model).
 * - EmergencyContacts: email, address, primary flag; wider phone columns.
 * - EmployeeDependents: gender; more relationship values.
 * - EmployeeCareer (Work History): pay grade, location, employment type, change reason;
 *   backfilled with one open row per active employee that has no history.
 * - EmployeeSalaryHistory, backfilled from the current EmployeeSalary lines.
 * - Employees.timezone is now required: employees without one get the time zone
 *   of their department, or of the nearest parent structure that has one. That is
 *   the time zone they were already shown and treated as being in.
 *
 * Every step checks the current schema first so the migration is safe to re-run.
 */
class v20261123_100000_employee_fields extends AbstractMigration
{
    public function up()
    {
        $this->createLookups();
        $this->addEmployeeColumns();
        $this->createBankAccounts();
        $this->updateEmergencyContacts();
        $this->updateDependents();
        $this->updateWorkHistory();
        $this->createSalaryHistory();
        $this->addFieldNameMappings();
        $this->addSettings();
        $this->backfillEmploymentType();
        $this->backfillWorkHistory();
        $this->backfillSalaryHistory();
        $this->backfillEmployeeTimeZones();

        return true;
    }

    public function down()
    {
        return true;
    }

    private function createLookups()
    {
        $this->executeQuery(<<<'SQL'
create table if not exists `WorkLocations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(250) default NULL,
  `city` varchar(150) default NULL,
  `country` char(2) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  primary key (`id`)
) engine=innodb default charset=utf8;
SQL
        );

        $this->executeQuery(<<<'SQL'
create table if not exists `CostCenters` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) default NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  primary key (`id`)
) engine=innodb default charset=utf8;
SQL
        );

        $this->executeQuery(<<<'SQL'
create table if not exists `TerminationReasons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  primary key (`id`)
) engine=innodb default charset=utf8;
SQL
        );

        if ($this->countRows('TerminationReasons') === 0) {
            $reasons = array(
                'Resignation - new job',
                'Resignation - personal reasons',
                'Resignation - relocation',
                'Retirement',
                'End of contract',
                'Performance',
                'Misconduct',
                'Redundancy / layoff',
                'Mutual agreement',
                'Other',
            );
            $now = date('Y-m-d H:i:s');
            foreach ($reasons as $reason) {
                $this->db()->Execute(
                    'INSERT INTO TerminationReasons (name, created, updated) VALUES (?, ?, ?)',
                    array($reason, $now, $now)
                );
            }
        }
    }

    private function addEmployeeColumns()
    {
        $columns = array(
            'contract_end_date' => 'DATE NULL',
            'weekly_hours' => 'DECIMAL(5,2) NULL',
            'fte' => 'DECIMAL(4,3) NULL',
            'work_location' => 'BIGINT(20) NULL',
            'work_mode' => 'VARCHAR(20) NULL',
            'employment_type' => 'VARCHAR(30) NULL',
            'original_hire_date' => 'DATE NULL',
            'cost_center' => 'BIGINT(20) NULL',
            'last_working_day' => 'DATE NULL',
            'termination_type' => 'VARCHAR(30) NULL',
            'termination_reason' => 'BIGINT(20) NULL',
            'eligible_for_rehire' => 'VARCHAR(20) NULL',
            'regrettable' => 'VARCHAR(5) NULL',
            'about' => 'TEXT NULL',
            'linkedin_url' => 'VARCHAR(250) NULL',
        );
        foreach ($columns as $name => $definition) {
            $this->addColumn('Employees', $name, $definition);
        }

        $this->addForeignKey('Employees', 'Fk_Employee_WorkLocation', 'work_location', 'WorkLocations');
        $this->addForeignKey('Employees', 'Fk_Employee_CostCenter', 'cost_center', 'CostCenters');
        $this->addForeignKey('Employees', 'Fk_Employee_TerminationReason', 'termination_reason', 'TerminationReasons');
    }

    private function createBankAccounts()
    {
        $this->executeQuery(<<<'SQL'
create table if not exists `EmployeeBankAccounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `account_holder` varchar(150) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `branch` varchar(150) default NULL,
  `account_number` text default NULL,
  `account_number_last4` varchar(4) default NULL,
  `iban` text default NULL,
  `iban_last4` varchar(4) default NULL,
  `swift_bic` varchar(20) default NULL,
  `routing_code` varchar(30) default NULL,
  `currency` bigint(20) default NULL,
  `is_primary` varchar(3) default 'No',
  `split_type` varchar(20) default NULL,
  `split_value` decimal(12,2) default NULL,
  `notes` varchar(500) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  primary key (`id`),
  KEY `KEY_EmployeeBankAccounts_employee` (`employee`),
  CONSTRAINT `Fk_EmployeeBankAccounts_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL
        );
    }

    private function updateEmergencyContacts()
    {
        $this->addColumn('EmergencyContacts', 'email', 'VARCHAR(100) NULL');
        $this->addColumn('EmergencyContacts', 'address', 'VARCHAR(250) NULL');
        $this->addColumn('EmergencyContacts', 'is_primary', "VARCHAR(3) NULL");
        foreach (array('home_phone', 'work_phone', 'mobile_phone') as $phone) {
            $this->executeQuery("ALTER TABLE `EmergencyContacts` MODIFY COLUMN `$phone` VARCHAR(30) NULL");
        }
    }

    private function updateDependents()
    {
        $this->addColumn('EmployeeDependents', 'gender', 'VARCHAR(20) NULL');
        $this->executeQuery(
            "ALTER TABLE `EmployeeDependents` MODIFY COLUMN `relationship` "
            . "enum('Child','Spouse','Partner','Parent','Sibling','Other') default NULL"
        );
    }

    private function updateWorkHistory()
    {
        $this->addColumn('EmployeeCareer', 'pay_grade', 'BIGINT(20) NULL');
        $this->addColumn('EmployeeCareer', 'work_location', 'BIGINT(20) NULL');
        $this->addColumn('EmployeeCareer', 'employment_type', 'VARCHAR(30) NULL');
        $this->addColumn('EmployeeCareer', 'change_reason', 'VARCHAR(30) NULL');
    }

    private function createSalaryHistory()
    {
        $this->executeQuery(<<<'SQL'
create table if not exists `EmployeeSalaryHistory` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `salary` bigint(20) default NULL,
  `component` bigint(20) default NULL,
  `old_amount` decimal(10,2) default NULL,
  `new_amount` decimal(10,2) default NULL,
  `currency` bigint(20) default NULL,
  `pay_frequency` varchar(20) default NULL,
  `effective_date` date NOT NULL,
  `change_reason` varchar(30) default NULL,
  `changed_by` bigint(20) default NULL,
  `created` datetime default NULL,
  primary key (`id`),
  KEY `KEY_EmployeeSalaryHistory_employee` (`employee`),
  KEY `KEY_EmployeeSalaryHistory_effective_date` (`effective_date`),
  CONSTRAINT `Fk_EmployeeSalaryHistory_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL
        );
    }

    private function addFieldNameMappings()
    {
        // Labels for the new Employee columns, so they can be renamed or hidden from
        // Field Names like the existing ones. The three name columns existed already
        // but were never exposed.
        $fields = array(
            'name_preferred' => 'Preferred Name',
            'pronouns' => 'Pronouns',
            'name_pronunciation' => 'Name Pronunciation',
            'driving_license_exp_date' => 'Driving License Expiry',
            'about' => 'About',
            'linkedin_url' => 'LinkedIn',
            'employment_type' => 'Employment Type',
            'work_location' => 'Work Location',
            'work_mode' => 'Work Arrangement',
            'weekly_hours' => 'Weekly Hours',
            'fte' => 'Full-time Equivalent (FTE)',
            'contract_end_date' => 'Contract End Date',
            'original_hire_date' => 'Original Hire Date',
            'cost_center' => 'Cost Center',
            'last_working_day' => 'Last Working Day',
            'termination_type' => 'Termination Type',
            'termination_reason' => 'Termination Reason',
            'eligible_for_rehire' => 'Eligible for Rehire',
            'regrettable' => 'Regrettable',
        );
        foreach ($fields as $name => $label) {
            $mapping = new FieldNameMapping();
            $mapping->Load('type = ? and name = ?', array('Employee', $name));
            if (!empty($mapping->id)) {
                continue;
            }
            $mapping->type = 'Employee';
            $mapping->name = $name;
            $mapping->textOrig = $label;
            $mapping->textMapped = $label;
            $mapping->display = 'Form';
            $mapping->Save();
        }

        // confirmation_date is used as the probation end date. Rename the label unless
        // the customer has already given it their own name.
        $mapping = new FieldNameMapping();
        $mapping->Load('type = ? and name = ?', array('Employee', 'confirmation_date'));
        if (!empty($mapping->id)) {
            if ($mapping->textMapped === 'Confirmation Date') {
                $mapping->textMapped = 'Probation End Date';
            }
            $mapping->textOrig = 'Probation End Date';
            $mapping->Save();
        }
    }

    private function addSettings()
    {
        // The hours of a full-time week, used to work out an employee's FTE from their
        // weekly hours. Leave it empty to enter FTE by hand only.
        $setting = new Setting();
        $setting->Load('name = ?', array('Company: Standard Weekly Hours'));
        if (!empty($setting->id)) {
            return;
        }
        $setting->name = 'Company: Standard Weekly Hours';
        $setting->value = '40';
        $setting->description = 'Hours in a full-time work week. An employee\'s FTE is worked out from their weekly hours using this value (40 hours gives 1.0, 20 hours gives 0.5). Leave it empty to enter FTE by hand.';
        $setting->meta = '';
        $setting->category = 'Company';
        $setting->setting_group = null;
        $setting->setting_order = 12;
        $setting->Save();
    }

    private function backfillEmployeeTimeZones()
    {
        // Climb at most six levels of the company structure, nearest first.
        for ($depth = 0; $depth < 6; $depth++) {
            $joins = 'JOIN CompanyStructures c0 ON c0.id = e.department';
            for ($i = 1; $i <= $depth; $i++) {
                $joins .= sprintf(' JOIN CompanyStructures c%d ON c%d.id = c%d.parent', $i, $i, $i - 1);
            }
            $this->executeQuery(sprintf(
                "UPDATE Employees e %s SET e.timezone = c%d.timezone"
                . " WHERE (e.timezone IS NULL OR e.timezone = '')"
                . " AND c%d.timezone IS NOT NULL AND c%d.timezone <> ''",
                $joins,
                $depth,
                $depth,
                $depth
            ));
        }
    }

    private function backfillEmploymentType()
    {
        // The seeded employment statuses mix type and contract ("Full Time Permanent").
        // Derive the new employment_type from the status name where it is obvious.
        $this->executeQuery(<<<'SQL'
UPDATE Employees e
JOIN EmploymentStatus s ON s.id = e.employment_status
SET e.employment_type = CASE
    WHEN s.name LIKE '%Intern%' THEN 'Intern'
    WHEN s.name LIKE 'Part Time%' THEN 'Part-time'
    WHEN s.name LIKE 'Full Time%' THEN 'Full-time'
    ELSE NULL
END
WHERE e.employment_type IS NULL
SQL
        );
    }

    private function backfillWorkHistory()
    {
        // One open Work History row per active employee that has none, starting on the
        // joined date, so later job changes have a row to close.
        $this->executeQuery(<<<'SQL'
INSERT INTO EmployeeCareer
  (employee, job_title, employment_status, department, supervisor, pay_grade,
   work_location, employment_type, date_start, date_end, change_reason, details, created, updated)
SELECT e.id, e.job_title, e.employment_status, e.department, e.supervisor, e.pay_grade,
   e.work_location, e.employment_type,
   CASE WHEN e.joined_date IS NULL OR e.joined_date < '1900-01-02' THEN CURDATE() ELSE e.joined_date END, NULL, 'Hire',
   'Created from the employee record', NOW(), NOW()
FROM Employees e
WHERE e.status = 'Active'
  AND NOT EXISTS (SELECT 1 FROM EmployeeCareer c WHERE c.employee = e.id)
SQL
        );
    }

    private function backfillSalaryHistory()
    {
        $this->executeQuery(<<<'SQL'
INSERT INTO EmployeeSalaryHistory
  (employee, salary, component, old_amount, new_amount, currency, pay_frequency,
   effective_date, change_reason, changed_by, created)
SELECT s.employee, s.id, s.component, NULL, s.amount, s.currency, s.pay_frequency,
   CASE WHEN e.joined_date IS NULL OR e.joined_date < '1900-01-02' THEN CURDATE() ELSE e.joined_date END, 'New hire', NULL, NOW()
FROM EmployeeSalary s
JOIN Employees e ON e.id = s.employee
WHERE NOT EXISTS (SELECT 1 FROM EmployeeSalaryHistory h WHERE h.salary = s.id)
SQL
        );
    }

    private function columnExists($table, $column)
    {
        $rows = $this->db()->Execute(
            'SELECT COUNT(*) AS c FROM information_schema.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            array($table, $column)
        );
        return is_array($rows) && !empty($rows[0]['c']);
    }

    private function constraintExists($table, $name)
    {
        $rows = $this->db()->Execute(
            'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            array($table, $name)
        );
        return is_array($rows) && !empty($rows[0]['c']);
    }

    private function countRows($table)
    {
        $rows = $this->db()->Execute("SELECT COUNT(*) AS c FROM `$table`");
        return is_array($rows) ? (int) $rows[0]['c'] : 0;
    }

    private function addColumn($table, $column, $definition)
    {
        if (!$this->columnExists($table, $column)) {
            $this->executeQuery("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private function addForeignKey($table, $name, $column, $refTable)
    {
        if (!$this->constraintExists($table, $name)) {
            $this->executeQuery(
                "ALTER TABLE `$table` ADD CONSTRAINT `$name` FOREIGN KEY (`$column`) "
                . "REFERENCES `$refTable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE"
            );
        }
    }
}
