<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\FileService;
use Model\File;
use Utils\LogManager;

/**
 * Removes the employee record of a hire whose onboarding was cancelled before they
 * started, together with everything stored for them.
 *
 * Most employee data goes through the foreign keys on Employees (ON DELETE CASCADE:
 * documents, form assignments and answers, bank accounts, contacts, signed documents,
 * course enrolments, tasks, leave, time, payroll employees, ...). This class handles
 * what the foreign keys do not: tables without a key, the login, files on disk and
 * assets handed to the hire.
 */
class HireRecordRemover
{
    /** Tables that store an employee id without a foreign key: table => columns. */
    const LOOSE_TABLES = array(
        'Notifications' => array('employee', 'fromEmployee'),
        'AuditLog' => array('employee'),
        'EsignAuditLogs' => array('employee'),
        'LeaveRules' => array('employee'),
        'LeaveStartingBalance' => array('employee'),
        'PayrollData' => array('employee'),
        'Reactions' => array('employee'),
        'ConversationUserStatus' => array('employee'),
    );

    /**
     * Why this employee cannot be removed, or null when they can.
     */
    public function blocker(array $onboarding)
    {
        $employeeId = (int) $onboarding['employee'];
        $rows = Db::rows('SELECT status FROM Employees WHERE id = ?', array($employeeId));
        if (empty($rows)) {
            return null;
        }
        if (!empty($onboarding['activated_at']) || $rows[0]['status'] === 'Active') {
            return 'This employee is active, so the onboarding cannot be deleted. Remove the employee from Employees instead.';
        }
        $other = Db::rows(
            "SELECT id FROM Onboardings WHERE employee = ? AND id <> ? AND status <> 'Cancelled' LIMIT 1",
            array($employeeId, (int) $onboarding['id'])
        );
        if (!empty($other)) {
            return 'This employee has another onboarding that is not cancelled.';
        }
        return null;
    }

    /**
     * Delete the employee and their data. Database rows go in one transaction; files are
     * removed from disk only after it commits.
     */
    public function remove($employeeId)
    {
        $employeeId = (int) $employeeId;
        $files = $this->collectFiles($employeeId);
        $esignPaths = $this->collectEsignPaths($employeeId);

        Db::transaction(function () use ($employeeId, $files) {
            // Other onboardings where this person was the manager, buddy or a step owner.
            $this->exec('UPDATE Onboardings SET supervisor = NULL WHERE supervisor = ?', array($employeeId));
            $this->exec('UPDATE Onboardings SET buddy = NULL WHERE buddy = ?', array($employeeId));
            $this->exec('UPDATE OnboardingSteps SET owner_employee = NULL WHERE owner_employee = ?', array($employeeId));

            if ($this->tableExists('CompanyAssets')) {
                $this->exec(
                    "UPDATE CompanyAssets SET employee = NULL, status = 'Available' WHERE employee = ? AND status = 'Assigned'",
                    array($employeeId)
                );
            }
            if ($this->tableExists('EsignDocuments')) {
                // Per-person copies made for signing; their EsignedDocs rows cascade.
                $this->exec('DELETE FROM EsignDocuments WHERE assigned_employee = ?', array($employeeId));
            }
            foreach (self::LOOSE_TABLES as $table => $columns) {
                if (!$this->tableExists($table)) {
                    continue;
                }
                foreach ($columns as $column) {
                    $this->exec("DELETE FROM `$table` WHERE `$column` = ?", array($employeeId));
                }
            }
            if ($this->tableExists('UserInvitations')) {
                $this->exec(
                    "DELETE FROM UserInvitations WHERE context = 'onboarding' AND context_ref IN (SELECT id FROM Onboardings WHERE employee = ?)",
                    array($employeeId)
                );
            }
            $this->exec('DELETE FROM Users WHERE employee = ?', array($employeeId));
            foreach ($files as $file) {
                $this->exec('DELETE FROM Files WHERE id = ?', array((int) $file->id));
            }
            // Cascades to the remaining employee tables, including Onboardings.
            $this->exec('DELETE FROM Employees WHERE id = ?', array($employeeId));
        });

        foreach ($files as $file) {
            $this->deleteFromDisk($file);
        }
        $dataDir = BaseService::getInstance()->getDataDirectory();
        foreach ($esignPaths as $path) {
            if (strpos($path, '..') === false && strpos($path, 'esign/') === 0 && is_file($dataDir . $path)) {
                @unlink($dataDir . $path);
            }
        }
    }

    /** Files rows owned by the employee or attached to their documents. */
    private function collectFiles($employeeId)
    {
        $names = array();
        foreach (Db::rows('SELECT name FROM Files WHERE employee = ?', array($employeeId)) as $row) {
            $names[$row['name']] = true;
        }
        if ($this->tableExists('EmployeeDocuments')) {
            $rows = Db::rows(
                "SELECT attachment FROM EmployeeDocuments WHERE employee = ? AND attachment IS NOT NULL AND attachment <> ''",
                array($employeeId)
            );
            foreach ($rows as $row) {
                $names[$row['attachment']] = true;
            }
        }
        $files = array();
        foreach (array_keys($names) as $name) {
            $file = new File();
            $file->Load('name = ?', array($name));
            if (!empty($file->id)) {
                $files[] = $file;
            }
        }
        return $files;
    }

    /** PDFs of signing copies and signed documents (stored as paths, not Files rows). */
    private function collectEsignPaths($employeeId)
    {
        $paths = array();
        if ($this->tableExists('EsignDocuments')) {
            $rows = Db::rows(
                "SELECT pdf_attachment AS p FROM EsignDocuments WHERE assigned_employee = ? AND pdf_attachment IS NOT NULL AND pdf_attachment <> ''",
                array($employeeId)
            );
            foreach ($rows as $row) {
                $paths[] = $row['p'];
            }
        }
        if ($this->tableExists('EsignedDocs')) {
            $rows = Db::rows(
                "SELECT pdf_file AS p FROM EsignedDocs WHERE employee = ? AND pdf_file IS NOT NULL AND pdf_file <> ''",
                array($employeeId)
            );
            foreach ($rows as $row) {
                $paths[] = $row['p'];
            }
        }
        return array_unique($paths);
    }

    private function deleteFromDisk(File $file)
    {
        try {
            if (empty($file->filename)) {
                return;
            }
            $local = BaseService::getInstance()->getDataDirectory() . $file->filename;
            $s3 = \Classes\SettingsManager::getInstance()->getSetting('Files: Upload Files to S3') == '1';
            if ($s3 || is_file($local)) {
                FileService::getInstance()->deleteFileFromDisk($file);
            }
        } catch (\Exception $e) {
            LogManager::getInstance()->error('Onboarding: could not delete file ' . $file->name . ': ' . $e->getMessage());
        }
    }

    private function exec($sql, array $params)
    {
        if (Db::db()->Execute($sql, $params) === false) {
            throw new \Exception('Onboarding: removing the employee failed: ' . Db::db()->ErrorMsg());
        }
    }

    private function tableExists($table)
    {
        static $cache = array();
        if (!isset($cache[$table])) {
            // Not "SHOW TABLES LIKE ?": SHOW does not take a placeholder here.
            $rows = Db::rows(
                'SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                array($table)
            );
            $cache[$table] = !empty($rows) && (int) $rows[0]['c'] > 0;
        }
        return $cache[$table];
    }
}
