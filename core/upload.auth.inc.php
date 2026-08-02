<?php
/**
 * Authentication and ownership gate for the standalone upload handlers
 * (fileupload.php, common-upload.php, editor-upload.php — the latter two also reached
 * through the fileupload-new.php dispatcher).
 *
 * Those scripts sit directly under the web root but, unlike service.php and data.php,
 * never rejected an anonymous caller: server.includes.inc.php only *populates* $user
 * from the session. An unauthenticated request could therefore write files into the
 * data directory, overwrite an existing Files row (and the bytes on disk), reassign a
 * file to any employee, and delete any user's profile image.
 *
 * Include this immediately after server.includes.inc.php, which defines $user and the
 * SIGN_IN_ELEMENT_MAPPING_FIELD_NAME constant these helpers rely on.
 */

use Classes\BaseService;
use Utils\SessionUtils;

// fileupload-new.php includes only one of common-upload/editor-upload per request, but
// guard anyway so a future double-include cannot fatal on redeclaration.
if (!function_exists('iceUploadDeny')) {
    /**
     * Refuse the request. Handlers here answer with JSON (the legacy fileupload.php
     * form posts into an iframe, where a JSON body is inert and simply shows nothing).
     */
    function iceUploadDeny($code, $message)
    {
        http_response_code(403);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(array('status' => 'ERROR', 'code' => $code, 'error' => $message));
        exit();
    }

    /**
     * Admins and managers legitimately upload on behalf of other employees (HR
     * attaching a contract, setting someone's profile image). Everyone else is
     * confined to their own employee record.
     */
    function iceUploadCallerIsPrivileged($user)
    {
        return in_array(
            $user->user_level,
            array('Admin', 'Manager', 'Restricted Admin', 'Restricted Manager'),
            true
        );
    }

    /**
     * May $user attach a file to — or delete files belonging to — employee $ownerId?
     * A null/empty/_NONE_ owner means the file is not tied to an employee at all.
     */
    function iceUploadMayActOnEmployee($user, $ownerId)
    {
        if ($ownerId === null || $ownerId === '' || $ownerId === '_NONE_') {
            return true;
        }
        if (iceUploadCallerIsPrivileged($user)) {
            return true;
        }
        $ownProfileId = BaseService::getInstance()->getCurrentProfileId();

        return !empty($ownProfileId) && intval($ownerId) === intval($ownProfileId);
    }

    /**
     * The handlers reuse an existing Files row whenever one already carries the
     * requested name, then overwrite the bytes on disk. Refuse when that row belongs
     * to a *different* employee.
     *
     * An owner-less row (settings assets, editor images) stays replaceable by any
     * authenticated caller: it holds no employee's data, and tightening it further
     * would break the settings and editor upload flows.
     */
    function iceUploadMayReplaceFile($user, $file)
    {
        if (empty($file->id)) {
            return true; // new row, nothing to overwrite
        }
        if (iceUploadCallerIsPrivileged($user)) {
            return true;
        }
        $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
        $owner = $file->$signInMappingField;
        if (empty($owner)) {
            return true;
        }
        $ownProfileId = BaseService::getInstance()->getCurrentProfileId();

        return !empty($ownProfileId) && intval($owner) === intval($ownProfileId);
    }
}

if (!isset($user) || empty($user)) {
    $user = SessionUtils::getSessionObject('user');
}

$iceUploadUserLevels = array(
    'Admin',
    'Manager',
    'Employee',
    'Restricted Admin',
    'Restricted Manager',
    'Restricted Employee',
);

if (empty($user) || empty($user->id) || empty($user->email)
    || !in_array($user->user_level, $iceUploadUserLevels, true)
) {
    iceUploadDeny('NO_USER_FOUND', 'Authentication required');
}
