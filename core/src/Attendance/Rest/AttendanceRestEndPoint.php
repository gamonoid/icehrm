<?php
namespace Attendance\Rest;

use Attendance\Common\Model\Attendance;
use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\IceResponse;
use Classes\LanguageManager;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Users\Common\Model\User;
use Utils\LogManager;

class AttendanceRestEndPoint extends RestEndPoint
{
    const ELEMENT_NAME = 'Attendance';

    public function getModelObject($id)
    {
        $obj = new Attendance();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user)
    {
        $query = new DataQuery('Attendance');
        $query->addColumn('id');
        $query->addColumn('employee');
        $query->addColumn('in_time');
        $query->addColumn('out_time');
        $query->addColumn('note');

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        if ($user->user_level !== 'Admin') {
            $query->setIsSubOrdinates(true);
        }

        return $this->listByQuery($query);
    }

    public function listEmployeeAttendance(User $user, $parameter)
    {
        $query = new DataQuery('Attendance');
        $query->addColumn('id');
        $query->addColumn('employee');
        $query->addColumn('in_time');
        $query->addColumn('out_time');
        $query->addColumn('note');

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        $query->addFilter(new Filter('employee', $parameter));

        return $this->listByQuery($query);
    }

    public function get(User $user, $parameter)
    {
        if (empty($parameter)) {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_ENTITY_NOT_FOUND, 404);
        }

        if ($user->user_level !== 'Admin' && !PermissionManager::manipulationAllowed(
            BaseService::getInstance()->getCurrentProfileId(),
            $this->getModelObject($parameter)
        )
        ) {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
        }

        $mapping = [
            "employee" => ["Employee", "id", "first_name+last_name"],
        ];

        $emp = BaseService::getInstance()->getElement(
            self::ELEMENT_NAME,
            $parameter,
            json_encode($mapping),
            true
        );

        $emp = $this->enrichElement($emp, $mapping);
        if (!empty($emp)) {
            $emp = $this->cleanObject($emp);
            return new IceResponse(IceResponse::SUCCESS, $emp);
        }
        return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_ENTITY_NOT_FOUND, 404);
    }

    public function post(User $user)
    {
        $body = $this->getRequestBody();

        $permissionResponse = $this->checkBasicPermissions($user, $body['employee']);
        if ($permissionResponse->getStatus() !== IceResponse::SUCCESS) {
            return $permissionResponse;
        }

        $response = BaseService::getInstance()->addElement(self::ELEMENT_NAME, $body);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            $response = $this->get($user, $response->getData()->id);
            $response->setCode(201);
            return $response;
        }

        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    protected function getServerTime()
    {
        $currentEmployeeTimeZone = BaseService::getInstance()->getCurrentEmployeeTimeZone();

        if (empty($currentEmployeeTimeZone)) {
            $currentEmployeeTimeZone = 'Asia/Colombo';
        }
        date_default_timezone_set('Asia/Colombo');

        $date = new \DateTime("now", new \DateTimeZone('Asia/Colombo'));

        $date->setTimezone(new \DateTimeZone($currentEmployeeTimeZone));
        return $date->format('Y-m-d H:i:s');
    }

    public function punchIn(User $user)
    {
        $body = $this->getRequestBody();

        if (empty($body['in_time'])) {
            $body['in_time'] = $this->getServerTime();
        }

        if (empty($body['in_time'])) {
            return new IceResponse(IceResponse::ERROR, 'User department timezone is not set', 400);
        }


        $openPunch = $this->getOpenPunch($user, $body['employee'], $body['in_time']);

        if ($openPunch->getStatus() === IceResponse::SUCCESS && !empty($openPunch->getData()['attendnace'])) {
            return new IceResponse(IceResponse::ERROR, 'User has already punched in for the day ', 400);
        }

        $permissionResponse = $this->checkBasicPermissions($user, $body['employee']);
        if ($permissionResponse->getStatus() !== IceResponse::SUCCESS) {
            return $permissionResponse;
        }

        $response = BaseService::getInstance()->addElement(self::ELEMENT_NAME, $body);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            $attendance = $this->cleanObject($response->getData());
            $response->setData($attendance);
            $response->setCode(201);
            return $response;
        }

        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    public function punchOut(User $user)
    {
        $body = $this->getRequestBody();

        if (empty($body['out_time'])) {
            $body['out_time'] = $this->getServerTime();
        }

        $attendance = $this->findAttendance($body['employee'], $body['out_time']);

        if (empty($body['out_time'])) {
            return new IceResponse(IceResponse::ERROR, 'User department timezone is not set', 400);
        }

        if ($attendance->employee.'' !== $body['employee'].'') {
            return new IceResponse(IceResponse::ERROR, 'User has not punched in for the day ', 400);
        }

        $permissionResponse = $this->checkBasicPermissions($user, $body['employee']);
        if ($permissionResponse->getStatus() !== IceResponse::SUCCESS) {
            return $permissionResponse;
        }

        $response = $this->savePunch(
            $body['employee'],
            $attendance->in_time,
            $body['note'],
            $body['out_time'],
            $attendance->id
        );

        if ($response->getStatus() === IceResponse::SUCCESS) {
            $attendance = $this->cleanObject($response->getData());
            $response->setData($attendance);
            $response->setCode(200);
            return $response;
        }

        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    protected function findAttendance($employeeId, $date)
    {
        if (strpos($date, ' ')) {
            $date = explode(' ', $date)[0];
        }
        $attendance = new Attendance();
        $attendance->Load(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ? and (out_time is NULL 
            or out_time = '0000-00-00 00:00:00')",
            array($employeeId,$date)
        );

        return $attendance;
    }

    public function getOpenPunch($user, $employeeId, $date)
    {
        if ($date === 'today') {
            $date = explode(' ', $this->getServerTime())[0];
        }

        $employee = BaseService::getInstance()->getElement('Employee', $employeeId, null, true);

        $attendance = $this->findAttendance($employee->id, $date);

        if ($attendance->employee === $employee->id) {
            //found an open punch
            $attendance = $this->cleanObject($attendance);
            return new IceResponse(
                IceResponse::SUCCESS,
                ['attendance' => $attendance, 'time' => $this->getServerTime()],
                200
            );
        } else {
            return new IceResponse(IceResponse::SUCCESS, ['time' => $this->getServerTime()], 200);
        }
    }

    protected function savePunch($employeeId, $inDateTime, $note = null, $outDateTime = null, $id = null)
    {
        $employee = BaseService::getInstance()->getElement(
            'Employee',
            $employeeId,
            null,
            true
        );
        $inDateArr = explode(" ", $inDateTime);
        $inDate = $inDateArr[0];

        $outDate = "";
        if (!empty($outDateTime)) {
            $outDateArr = explode(" ", $outDateTime);
            $outDate = $outDateArr[0];
        }

        //check if dates are differnet
        if (!empty($outDate) && $inDate != $outDate) {
            return new IceResponse(
                IceResponse::ERROR,
                LanguageManager::tran('Attendance entry should be within a single day')
            );
        }

        //compare dates
        if (!empty($outDateTime) && strtotime($outDateTime) <= strtotime($inDateTime)) {
            return new IceResponse(IceResponse::ERROR, 'Punch-in time should be earlier than Punch-out time');
        }

        //Find all punches for the day
        $attendance = new Attendance();
        $attendanceList = $attendance->Find(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ?",
            array($employee->id,$inDate)
        );

        foreach ($attendanceList as $attendance) {
            if (!empty($id) && $id == $attendance->id) {
                continue;
            }
            if (empty($attendance->out_time) || $attendance->out_time == "0000-00-00 00:00:00") {
                return new IceResponse(
                    IceResponse::ERROR,
                    "There is a non closed attendance entry for today. 
                    Please mark punch-out time of the open entry before adding a new one"
                );
            } elseif (!empty($outDateTime)) {
                if (strtotime($attendance->out_time) >= strtotime($outDateTime)
                    && strtotime($attendance->in_time) <= strtotime($outDateTime)) {
                    //-1---0---1---0 || ---0--1---1---0
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                } elseif (strtotime($attendance->out_time) >= strtotime($inDateTime)
                    && strtotime($attendance->in_time) <= strtotime($inDateTime)) {
                    //---0---1---0---1 || ---0--1---1---0
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                } elseif (strtotime($attendance->out_time) <= strtotime($outDateTime)
                    && strtotime($attendance->in_time) >= strtotime($inDateTime)) {
                    //--1--0---0--1--
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                }
            } else {
                if (strtotime($attendance->out_time) >= strtotime($inDateTime)
                    && strtotime($attendance->in_time) <= strtotime($inDateTime)) {
                    //---0---1---0
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                }
            }
        }

        $attendance = new Attendance();
        if (!empty($id)) {
            $attendance->Load("id = ?", array($id));
        }
        $attendance->in_time = $inDateTime;
        if (empty($outDateTime)) {
            $attendance->out_time = null;
        } else {
            $attendance->out_time = $outDateTime;
        }

        $attendance->employee = $employeeId;
        $attendance->note = $note;
        $ok = $attendance->Save();
        if (!$ok) {
            LogManager::getInstance()->info($attendance->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error occurred while saving attendance");
        }
        return new IceResponse(IceResponse::SUCCESS, $attendance);
    }
}
