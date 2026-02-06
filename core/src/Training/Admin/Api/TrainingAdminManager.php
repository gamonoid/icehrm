<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:46 AM
 */

namespace Training\Admin\Api;

use Classes\AbstractModuleManager;
use Training\Common\Model\Course;

class TrainingAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('Course');
        $this->addModelClass('TrainingSession');
        $this->addModelClass('TrainingSessionWithCourse');
        $this->addModelClass('CoordinatedTrainingSession');
    }

    public function getDashboardItemData()
    {
        $data = array();
        $course = new Course();
        $data['numberOfCourses'] = $course->Count("1 = 1");
        return $data;
    }
}
