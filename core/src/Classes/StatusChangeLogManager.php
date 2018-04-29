<?php
namespace Classes;

use Employees\Common\Model\Employee;
use Model\StatusChangeLog;
use Users\Common\Model\User;
use Utils\LogManager;

class StatusChangeLogManager
{

    private static $me = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new StatusChangeLogManager();
        }

        return self::$me;
    }

    public function addLog($type, $element, $userId, $oldStatus, $newStatus, $note)
    {
        $statusChangeLog = new StatusChangeLog();
        $statusChangeLog->type = $type;
        $statusChangeLog->element = $element;
        $statusChangeLog->user_id = $userId;
        $statusChangeLog->status_from = $oldStatus;
        $statusChangeLog->status_to = $newStatus;
        $statusChangeLog->created = date("Y-m-d H:i:s");
        $statusChangeLog->data = $note;
        $ok = $statusChangeLog->Save();
        if (!$ok) {
            LogManager::getInstance()->info($statusChangeLog->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, null);
        }

        return new IceResponse(IceResponse::SUCCESS, $statusChangeLog);
    }

    public function getLogs($type, $element)
    {
        $statusChangeLog = new StatusChangeLog();
        $logsTemp = $statusChangeLog->Find("type = ? and element = ? order by created", array($type, $element));
        $logs = array();
        foreach ($logsTemp as $statusChangeLog) {
            $t = array();
            $t['time'] = $statusChangeLog->created;
            $t['status_from'] = $statusChangeLog->status_from;
            $t['status_to'] = $statusChangeLog->status_to;
            $t['time'] = $statusChangeLog->created;
            $userName = null;
            if (!empty($statusChangeLog->user_id)) {
                $lgUser = new User();
                $lgUser->Load("id = ?", array($statusChangeLog->user_id));
                if ($lgUser->id == $statusChangeLog->user_id) {
                    if (!empty($lgUser->employee)) {
                        $lgEmployee = new Employee();
                        $lgEmployee->Load("id = ?", array($lgUser->employee));
                        $userName = $lgEmployee->first_name." ".$lgEmployee->last_name;
                    } else {
                        $userName = $lgUser->userName;
                    }
                }
            }

            if (!empty($userName)) {
                $t['note'] = $statusChangeLog->data." (by: ".$userName.")";
            } else {
                $t['note'] = $statusChangeLog->data;
            }

            $logs[] = $t;
        }

        return new IceResponse(IceResponse::SUCCESS, $logs);
    }
}
