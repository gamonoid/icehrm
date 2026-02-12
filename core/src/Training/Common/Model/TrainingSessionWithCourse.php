<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:51 AM
 */

namespace Training\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class TrainingSessionWithCourse extends BaseModel
{
    public $table = 'TrainingSessions';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get", "element");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    // @codingStandardsIgnoreStart
    public function Load($where = null, $bindarr = false)
    {
        parent::Load($where, $bindarr);

        if (!empty($this->id)) {
            $course = new Course();
            $course->Load("id = ?", array($this->course));
            $this->coordinator = $course->coordinator;
            $this->courseDetails = $course->description;
            $this->trainer = $course->trainer;
            $this->trainer_info = $course->trainer_info;
            $this->paymentType = $course->paymentType;
            $this->cost = $course->cost;
            $this->courseStatus = $course->status;
        }

        return true;
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('training', 'admin'),
            new ModuleAccess('training', 'user'),
        ];
    }
}
