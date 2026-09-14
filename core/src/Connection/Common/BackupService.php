<?php

namespace Connection\Common;

use Classes\BaseService;
use Classes\IceResponse;
use Model\Backup;
use Utils\LogManager;

/**
 * Creates password-encrypted backups of the IceHRM database (and, later, files)
 * for the Connection module's "Backups" tab.
 *
 * DATABASE FORMAT (before encryption): a single self-describing JSON document —
 *   { "manifest": { app_version, db_name, tables: { <t>: {columns, row_count} } },
 *     "data":     { <t>: [ { <col>: <string|null|{"__b64":...}> }, ... ] } }
 * Every value is exported as a string or null; binary / non-UTF-8 bytes are
 * wrapped as {"__b64": base64} so the payload is lossless (Chinese, emoji, and
 * legacy-encoded data all survive). Column names travel with each row so a
 * target with extra/missing columns can reconcile on import.
 *
 * The JSON is then encrypted with the admin-supplied password (AES-256-GCM,
 * key derived via PBKDF2-SHA256) into a self-describing envelope, and zipped
 * when the Zip extension is available. Files live under app/data/db_backups.
 */
class BackupService
{
    const BACKUP_SUBDIR = 'db_backups';
    const FILES_BACKUP_SUBDIR = 'file_backups';

    /** Top-level entries under app/data that are never included in a file backup. */
    const FILE_BACKUP_EXCLUDES = array('keys', 'db_backups', 'file_backups', 'icehrm.log');

    /** Absolute path to the database-backups directory (created if missing). */
    public function getBackupDir()
    {
        return $this->ensureDir(self::BACKUP_SUBDIR);
    }

    /** Absolute path to the file-backups directory (created if missing). */
    public function getFilesBackupDir()
    {
        return $this->ensureDir(self::FILES_BACKUP_SUBDIR);
    }

    private function ensureDir($subDir)
    {
        $dir = rtrim(BaseService::getInstance()->getDataDirectory(), '/') . '/' . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0770, true);
        }
        return $dir;
    }

    /**
     * Build, encrypt, (zip,) store a full database backup and record it.
     *
     * @return IceResponse SUCCESS with the stored Backup row, or ERROR.
     */
    public function createDatabaseBackup($password, $name, $userId)
    {
        if (strlen((string) $password) < 6) {
            return new IceResponse(IceResponse::ERROR, 'A backup password of at least 6 characters is required.');
        }

        // Large DBs produce large JSON; give the request room.
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        try {
            list($json, $tableCount, $rowCount) = $this->buildDatabaseJson();
            $envelope = $this->encrypt($json, $password);
            unset($json);

            $stamp = date('Ymd_His');
            $base = 'icehrm-db-backup-' . $stamp;
            $dir = $this->getBackupDir();

            $zipped = false;
            if (class_exists('\\ZipArchive')) {
                $fileName = $base . '.zip';
                $fullPath = $dir . '/' . $fileName;
                $zip = new \ZipArchive();
                if ($zip->open($fullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    return new IceResponse(IceResponse::ERROR, 'Could not create the backup archive.');
                }
                $zip->addFromString($base . '.json.enc', $envelope);
                $zip->close();
                $zipped = true;
                $format = 'json-gcm-zip';
            } else {
                // Zip extension unavailable — store the encrypted JSON as-is.
                $fileName = $base . '.json.enc';
                $fullPath = $dir . '/' . $fileName;
                file_put_contents($fullPath, $envelope);
                $format = 'json-gcm';
            }
            unset($envelope);

            $backup = new Backup();
            $backup->name = !empty($name) ? $name : ('Database backup ' . date('Y-m-d H:i'));
            $backup->type = 'database';
            $backup->status = 'Completed';
            $backup->file_name = $fileName;
            $backup->file_path = self::BACKUP_SUBDIR . '/' . $fileName; // relative to app/data
            $backup->file_size = filesize($fullPath);
            $backup->format = $format;
            $backup->encrypted = 1;
            $backup->checksum = hash_file('sha256', $fullPath);
            $backup->meta = json_encode(array(
                'db' => defined('APP_DB') ? APP_DB : null,
                'app_version' => defined('VERSION') ? VERSION : null,
                'table_count' => $tableCount,
                'row_count' => $rowCount,
                'zipped' => $zipped,
                'cipher' => 'aes-256-gcm',
            ));
            $backup->created_by = $userId;
            $backup->created = date('Y-m-d H:i:s');

            if (!$backup->Save()) {
                @unlink($fullPath);
                return new IceResponse(IceResponse::ERROR, 'Backup created but could not be recorded.');
            }

            return new IceResponse(IceResponse::SUCCESS, $this->toArray($backup));
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('BackupService: database backup failed - ' . $e->getMessage());
            return new IceResponse(IceResponse::ERROR, 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Zip every file under app/data — except the keys/, db_backups/ and
     * file_backups/ directories and icehrm.log — into app/data/file_backups and
     * record it. When $password is given the archive is AES-256 encrypted (a
     * standard password-protected zip); otherwise it is a plain zip.
     *
     * @return IceResponse SUCCESS with the stored Backup row, or ERROR.
     */
    public function createFilesBackup($name, $userId, $password = '')
    {
        if (!class_exists('\\ZipArchive')) {
            return new IceResponse(
                IceResponse::ERROR,
                'The PHP Zip extension is required to create file backups.'
            );
        }

        $encrypt = ($password !== null && $password !== '');
        if ($encrypt && strlen((string) $password) < 6) {
            return new IceResponse(IceResponse::ERROR, 'A backup password of at least 6 characters is required.');
        }
        if ($encrypt && !defined('\\ZipArchive::EM_AES_256')) {
            return new IceResponse(
                IceResponse::ERROR,
                "This server's PHP/libzip build does not support encrypted zips. "
                . 'Leave the password blank for an unencrypted file backup.'
            );
        }

        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        try {
            $dataDir = rtrim(BaseService::getInstance()->getDataDirectory(), '/');
            $dir = $this->getFilesBackupDir();
            $stamp = date('Ymd_His');
            $fileName = 'icehrm-files-backup-' . $stamp . '.zip';
            $fullPath = $dir . '/' . $fileName;

            $zip = new \ZipArchive();
            if ($zip->open($fullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return new IceResponse(IceResponse::ERROR, 'Could not create the file backup archive.');
            }
            if ($encrypt) {
                $zip->setPassword($password);
            }

            $excludes = self::FILE_BACKUP_EXCLUDES;
            // Prune excluded top-level dirs (so we never descend into db_backups /
            // file_backups / keys) and skip icehrm.log at the root.
            $dirIt = new \RecursiveDirectoryIterator($dataDir, \FilesystemIterator::SKIP_DOTS);
            $filter = new \RecursiveCallbackFilterIterator(
                $dirIt,
                function ($current) use ($dataDir, $excludes) {
                    $rel = ltrim(str_replace('\\', '/', substr($current->getPathname(), strlen($dataDir))), '/');
                    $firstSeg = explode('/', $rel)[0];
                    return !in_array($firstSeg, $excludes, true);
                }
            );
            $it = new \RecursiveIteratorIterator($filter);

            $fileCount = 0;
            $totalBytes = 0;
            foreach ($it as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $path = $file->getPathname();
                $rel = ltrim(str_replace('\\', '/', substr($path, strlen($dataDir))), '/');
                $zip->addFile($path, $rel);
                if ($encrypt) {
                    $zip->setEncryptionName($rel, \ZipArchive::EM_AES_256);
                }
                $fileCount++;
                $totalBytes += $file->getSize();
            }
            $zip->close();

            $backup = new Backup();
            $backup->name = !empty($name) ? $name : ('Files backup ' . date('Y-m-d H:i'));
            $backup->type = 'files';
            $backup->status = 'Completed';
            $backup->file_name = $fileName;
            $backup->file_path = self::FILES_BACKUP_SUBDIR . '/' . $fileName;
            $backup->file_size = filesize($fullPath);
            $backup->format = $encrypt ? 'zip-aes256' : 'zip';
            $backup->encrypted = $encrypt ? 1 : 0;
            $backup->checksum = hash_file('sha256', $fullPath);
            $backup->meta = json_encode(array(
                'file_count' => $fileCount,
                'total_bytes' => $totalBytes,
                'source' => 'app/data',
                'excluded' => array_values($excludes),
                'encryption' => $encrypt ? 'zip-aes-256' : 'none',
            ));
            $backup->created_by = $userId;
            $backup->created = date('Y-m-d H:i:s');

            if (!$backup->Save()) {
                @unlink($fullPath);
                return new IceResponse(IceResponse::ERROR, 'Backup created but could not be recorded.');
            }

            return new IceResponse(IceResponse::SUCCESS, $this->toArray($backup));
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('BackupService: file backup failed - ' . $e->getMessage());
            return new IceResponse(IceResponse::ERROR, 'File backup failed: ' . $e->getMessage());
        }
    }

    /** All backups, newest first, as plain arrays for the API. */
    public function listBackups()
    {
        $db = BaseService::getInstance()->getDB();
        $rows = $db->Execute(
            'SELECT id, name, type, status, file_name, file_size, format, encrypted, '
            . 'checksum, meta, created_by, created FROM Backups ORDER BY id DESC'
        );
        return is_array($rows) ? $rows : array();
    }

    /** Stream a backup file to the browser (raw bytes — no JSON wrapping). */
    public function streamBackup($id)
    {
        $backup = new Backup();
        $backup->Load('id = ?', array($id));
        if (empty($backup->id)) {
            http_response_code(404);
            exit;
        }
        $path = rtrim(BaseService::getInstance()->getDataDirectory(), '/') . '/' . $backup->file_path;
        if (!is_file($path)) {
            http_response_code(404);
            exit;
        }
        // Replace the JSON content-type set upstream by rest.php.
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($backup->file_name) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-store');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        readfile($path);
        exit;
    }

    /** Delete a backup file + record. */
    public function deleteBackup($id)
    {
        $backup = new Backup();
        $backup->Load('id = ?', array($id));
        if (empty($backup->id)) {
            return new IceResponse(IceResponse::ERROR, 'Backup not found.');
        }
        $path = rtrim(BaseService::getInstance()->getDataDirectory(), '/') . '/' . $backup->file_path;
        if (is_file($path)) {
            @unlink($path);
        }
        $backup->Delete();
        return new IceResponse(IceResponse::SUCCESS, 'Backup deleted.');
    }

    // --- internals ----------------------------------------------------------

    /**
     * Build the plaintext backup JSON. Returns [json, tableCount, rowCount].
     */
    private function buildDatabaseJson()
    {
        $db = BaseService::getInstance()->getDB();
        $dbName = defined('APP_DB') ? APP_DB : $this->currentDbName($db);

        $tableRows = $db->Execute(
            "SELECT TABLE_NAME AS name FROM information_schema.TABLES "
            . "WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            array($dbName)
        );

        $manifestTables = array();
        $data = array();
        $rowCount = 0;

        foreach ((is_array($tableRows) ? $tableRows : array()) as $tr) {
            $table = $tr['name'];

            $colRows = $db->Execute(
                'SELECT COLUMN_NAME AS name, DATA_TYPE AS type, IS_NULLABLE AS nullable, '
                . 'COLUMN_KEY AS col_key, EXTRA AS extra, CHARACTER_SET_NAME AS charset '
                . 'FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? '
                . 'ORDER BY ORDINAL_POSITION',
                array($dbName, $table)
            );
            $columns = array();
            foreach ((is_array($colRows) ? $colRows : array()) as $c) {
                $columns[] = array(
                    'name' => $c['name'],
                    'type' => $c['type'],
                    'nullable' => $c['nullable'],
                    'key' => $c['col_key'],
                    'extra' => $c['extra'],
                    'charset' => $c['charset'],
                );
            }

            $rows = $db->Execute('SELECT * FROM `' . str_replace('`', '', $table) . '`');
            $encodedRows = array();
            foreach ((is_array($rows) ? $rows : array()) as $row) {
                $encodedRows[] = $this->encodeRow($row);
            }

            $manifestTables[$table] = array(
                'columns' => $columns,
                'row_count' => count($encodedRows),
            );
            $data[$table] = $encodedRows;
            $rowCount += count($encodedRows);
        }

        $payload = array(
            'manifest' => array(
                'format' => 'icehrm-db-backup',
                'version' => 1,
                'generated_at' => date('Y-m-d H:i:s'),
                'app_version' => defined('VERSION') ? VERSION : null,
                'db_name' => $dbName,
                'table_count' => count($manifestTables),
                'row_count' => $rowCount,
                'value_encoding' => 'string-or-null; binary/non-UTF8 wrapped as {"__b64":base64}',
                'tables' => $manifestTables,
            ),
            'data' => $data,
        );

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \Exception('Failed to encode backup JSON: ' . json_last_error_msg());
        }
        return array($json, count($manifestTables), $rowCount);
    }

    /** Encode a single DB row: NULL stays null, non-UTF8/binary → {"__b64":...}. */
    private function encodeRow($row)
    {
        $out = array();
        foreach ($row as $col => $val) {
            if ($val === null) {
                $out[$col] = null;
            } elseif (!mb_check_encoding($val, 'UTF-8')) {
                $out[$col] = array('__b64' => base64_encode($val));
            } else {
                $out[$col] = $val;
            }
        }
        return $out;
    }

    private function currentDbName($db)
    {
        $r = $db->Execute('SELECT DATABASE() AS db');
        return (is_array($r) && isset($r[0]['db'])) ? $r[0]['db'] : '';
    }

    /**
     * Encrypt with AES-256-GCM; key derived from the password via PBKDF2-SHA256.
     * Returns a self-describing JSON envelope (base64 fields) for portability.
     */
    private function encrypt($plaintext, $password)
    {
        $salt = random_bytes(16);
        $iterations = 100000;
        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
        $iv = random_bytes(12); // 96-bit nonce for GCM
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($ciphertext === false) {
            throw new \Exception('Encryption failed.');
        }
        return json_encode(array(
            'format' => 'icehrm-backup-enc',
            'version' => 1,
            'cipher' => 'aes-256-gcm',
            'kdf' => 'pbkdf2-sha256',
            'iterations' => $iterations,
            'salt' => base64_encode($salt),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'payload' => base64_encode($ciphertext),
        ));
    }

    /**
     * Decrypt an envelope produced by encrypt(). Returns the plaintext JSON, or
     * null on a wrong password / tampered data (GCM authentication fails). Used
     * by the import tooling; kept here so the format has one owner.
     */
    public static function decrypt($envelopeJson, $password)
    {
        $e = json_decode($envelopeJson, true);
        if (!is_array($e) || empty($e['payload'])) {
            return null;
        }
        $salt = base64_decode($e['salt']);
        $iterations = isset($e['iterations']) ? (int) $e['iterations'] : 100000;
        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
        $iv = base64_decode($e['iv']);
        $tag = base64_decode($e['tag']);
        $ct = base64_decode($e['payload']);
        $plaintext = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return $plaintext === false ? null : $plaintext;
    }

    private function toArray(Backup $b)
    {
        return array(
            'id' => $b->id,
            'name' => $b->name,
            'type' => $b->type,
            'status' => $b->status,
            'file_name' => $b->file_name,
            'file_size' => $b->file_size,
            'format' => $b->format,
            'encrypted' => $b->encrypted,
            'checksum' => $b->checksum,
            'meta' => $b->meta,
            'created_by' => $b->created_by,
            'created' => $b->created,
        );
    }
}
