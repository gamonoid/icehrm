<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:50 AM
 */

namespace Training\Common\Model;

use Classes\FileService;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class EmployeeTrainingSession extends BaseModel
{
    public $table = 'EmployeeTrainingSessions';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        // "get" stays own-rows only: EmployeeTrainingSession is a registered user
        // table, so get() applies the profile restriction to every list.
        return array("get","element","add","save","delete");
    }

    public function postProcessGetData($entry)
    {
        $trainingSession = new TrainingSession();
        $trainingSession->Load("id = ?", array($entry->trainingSession));
        $entry->attendanceType = $trainingSession->attendanceType;
        $entry->courseId = $trainingSession->course;
        
        // Add employee profile image
        $employee = new Employee();
        $employee->Load('id = ?', [$entry->employee]);
        $employee = FileService::getInstance()->updateSmallProfileImage($employee);
        $entry->image = $employee->image;
        
        return $entry;
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('training', 'admin'),
            new ModuleAccess('training', 'user'),
        ];
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
