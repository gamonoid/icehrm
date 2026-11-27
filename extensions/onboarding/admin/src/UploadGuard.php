<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Utils\LogManager;

/**
 * Document steps. The new hire uploads through onboarding/me/upload; the file is saved
 * with the employee as owner and the 'Onboarding' group, and a submitted step is only
 * accepted when it names a file that this employee uploaded here.
 */
class UploadGuard
{
    const FILE_GROUP = 'Onboarding';
    const MAX_FILE_BYTES = 10485760; // 10 MB
    const ALLOWED_EXTENSIONS = array('pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'rtf', 'odt');
    const IMAGE_EXTENSIONS = array('png', 'jpg', 'jpeg', 'gif', 'webp');

    public static function ownsFile($name, $employeeId)
    {
        if (!is_string($name) || !preg_match('/^onb_[a-f0-9]{24}$/', $name) || empty($employeeId)) {
            return false;
        }
        $rows = Db::rows(
            'SELECT id FROM Files WHERE name = ? AND employee = ? AND file_group = ? LIMIT 1',
            array($name, (int) $employeeId, self::FILE_GROUP)
        );
        return count($rows) > 0;
    }

    public static function handleUpload(RestEndPoint $rest, $employeeId)
    {
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'No file was uploaded', 400));
            return;
        }
        $upload = $_FILES['file'];
        if (!empty($upload['error']) || empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'The upload failed', 400));
            return;
        }
        $size = (int) $upload['size'];
        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Files are limited to 10 MB', 400));
            return;
        }
        $original = self::cleanName(isset($upload['name']) ? $upload['name'] : 'file');
        $ext = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $rest->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'That file type is not allowed. Use: ' . implode(', ', self::ALLOWED_EXTENSIONS),
                400
            ));
            return;
        }

        $name = 'onb_' . bin2hex(random_bytes(12));
        $target = BaseService::getInstance()->getDataDirectory() . $name . '.' . $ext;
        if (!@move_uploaded_file($upload['tmp_name'], $target)) {
            LogManager::getInstance()->error('Onboarding: could not move upload to ' . $target);
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not store the file', 500));
            return;
        }
        // An image extension must carry real image bytes, or a renamed HTML/SVG file
        // could be served inline.
        if (in_array($ext, self::IMAGE_EXTENSIONS, true) && @getimagesize($target) === false) {
            @unlink($target);
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That file is not a valid image', 400));
            return;
        }

        $file = FileService::getInstance()->saveFile($name, $ext, false, self::FILE_GROUP, (int) $employeeId);
        $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
            'name' => $file->name,
            'original' => $original,
            'size_text' => $file->size_text,
        ), 201));
    }

    /** A short-lived download link for an onboarding file, or null when it does not exist. */
    public static function fileUrl($name)
    {
        $rows = Db::rows('SELECT filename FROM Files WHERE name = ? AND file_group = ? LIMIT 1', array($name, self::FILE_GROUP));
        if (empty($rows)) {
            return null;
        }
        return FileService::getInstance()->getFileUrl($rows[0]['filename']);
    }

    private static function cleanName($name)
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[^\w.\- ]+/u', '_', $name);
        return mb_substr($name, 0, 150);
    }
}
