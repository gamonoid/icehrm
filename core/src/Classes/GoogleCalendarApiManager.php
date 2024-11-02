<?php
namespace Classes;

use Candidates\Common\Model\Candidate;
use Candidates\Common\Model\Interview;
use Classes\Exception\UserTokenNotFoundException;
use Company\Common\Model\Timezone;
use DateTime;
use Employees\Common\Model\Employee;
use Google_Service_Calendar_EventDateTime;
use JobPositions\Common\Model\Job;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;
use Leaves\Common\Model\LeaveType;
use Users\Common\Model\User;
use Utils\LogManager;

class GoogleCalendarApiManager
{
    protected static $services = [];

    const DEFAULT_CALENDAR = 'primary';

    /**
     * @param $user
     * @return \Google_Service_Calendar|mixed
     * @throws Exception\GoogleSyncException
     * @throws UserTokenNotFoundException
     */
    public static function getCalendarService($user)
    {
        $userId = $user->id;
        if (self::$services[$userId]) {
            return self::$services[$userId];
        }

        $accessToken = GoogleUserDataManager::getAccessToken($user);
        if (!$accessToken) {
            throw new UserTokenNotFoundException('User google access token not found');
        }

        $client = GoogleUserDataManager::getGoogleApiClient();
        $client->setAccessToken($accessToken);
        self::$services[$userId] = new \Google_Service_Calendar($client);

        return self::$services[$userId];
    }

    public static function createEventFromEmployeeLeave(EmployeeLeave $leave)
    {

        try {
            $user = self::getUserFromLeave($leave);
            $service = self::getCalendarService($user);
        } catch (Exception\GoogleSyncException $e) {
            LogManager::getInstance()->error($e->getMessage());
            return false;
        } catch (UserTokenNotFoundException $e) {
            LogManager::getInstance()->error($e->getMessage());
            return false;
        }

        $leaveType = new LeaveType();
        $leaveType->Load('id = ?', [$leave->leave_type]);

        $leaveDay = new EmployeeLeaveDay();
        $leaveDays = $leaveDay->Find('employee_leave = ? order by leave_date', [$leave->id]);
        $timezone = BaseService::getInstance()->getUserTimeZone($user);
        if (empty($timezone)) {
            $timezone = 'America/Los_Angeles';
        }

        $leaveDaysByType = [];
        $prevLeveType = null;
        foreach ($leaveDays as $leaveDay) {
            if ($prevLeveType == null || $prevLeveType != $leaveDay->leave_type) {
                $leaveDaysByType[] = [];
            }
            $leaveDaysByType[count($leaveDaysByType) - 1][] = $leaveDay;
            $prevLeveType = $leaveDay->leave_type;
        }

        $eventList = [];
        foreach ($leaveDaysByType as $leaveDayList) {
            $timing = LeaveUtil::getLeaveStartAndEndTimes($leaveDayList[0]->leave_type);
            $start = $leaveDayList[0]->leave_date.'T'.$timing[0];
            $end = $leaveDayList[count($leaveDayList) - 1]->leave_date.'T'.$timing[1];


            $event = new \Google_Service_Calendar_Event(
                array(
                'summary' => sprintf('%s (%s)', $leaveType->name, $leaveDayList[0]->leave_type),
                'description' => 'Leave request pending approval',
                'start' => array(
                    'dateTime' => $start,
                    'timeZone' => $timezone,
                ),
                'end' => array(
                    'dateTime' => $end,
                    'timeZone' => $timezone,
                ),
                'attendees' => array(
                    array('email' => BaseService::getInstance()->getCurrentUser()->email),
                ),
                'colorId' => 5,
                'reminders' => array(
                    'useDefault' => false,
                    'overrides' => array(
                        array('method' => 'email', 'minutes' => 24 * 60),
                        array('method' => 'popup', 'minutes' => 10),
                    ),
                ),
                )
            );

            try {
                $event = $service->events->insert(self::DEFAULT_CALENDAR, $event);
            } catch (\Exception $e) {
                LogManager::getInstance()->error($e->getMessage());
            }

            $eventList[] = $event;
        }

        $leave->event_ids = json_encode(
            array_map(
                function ($item) {
                    return $item->getId();
                },
                $eventList
            )
        );

        $leave->Save();

        return $eventList;
    }

    public static function approveLeaveEvents(EmployeeLeave $leave)
    {

        try {
            $user = self::getUserFromLeave($leave);
            $service = self::getCalendarService($user);
        } catch (Exception\GoogleSyncException $e) {
            return false;
        } catch (UserTokenNotFoundException $e) {
            return false;
        }

        if (empty($leave->event_ids)) {
            return [];
        }

        $eventIds = json_decode($leave->event_ids, true);
        $resp = [];
        foreach ($eventIds as $id) {
            try {
                $event = $service->events->get(self::DEFAULT_CALENDAR, $id);
                $event->setColorId(1);
                $event->setDescription('Leave request approved');
                $resp[] = $service->events->update(self::DEFAULT_CALENDAR, $id, $event);
            } catch (\Exception $e) {
            }
        }

        return $resp;
    }

    public static function deleteLeaveEvents(EmployeeLeave $leave)
    {
        try {
            $user = self::getUserFromLeave($leave);
            $service = self::getCalendarService($user);
        } catch (Exception\GoogleSyncException $e) {
            return false;
        } catch (UserTokenNotFoundException $e) {
            return false;
        }
        if (empty($leave->event_ids)) {
            return [];
        }

        $eventIds = json_decode($leave->event_ids, true);
        $resp = [];
        foreach ($eventIds as $id) {
            try {
                $resp[] = $service->events->delete(self::DEFAULT_CALENDAR, $id);
            } catch (\Exception $e) {
            }
        }

        return $resp;
    }

    protected static function getUserFromLeave(EmployeeLeave $leave)
    {
        $user = new User();
        $user->Load('employee = ?', [$leave->employee]);
        if (empty($user->id)) {
            throw new UserTokenNotFoundException();
        }

        return $user;
    }
}
