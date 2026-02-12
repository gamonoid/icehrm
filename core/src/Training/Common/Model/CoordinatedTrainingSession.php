<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:45 AM
 */

namespace Training\Common\Model;

use Classes\BaseService;

class CoordinatedTrainingSession extends TrainingSession
{

    public function getUserAccess()
    {
        $course = new Course();
        $course->Load('id = ?', array($this->course));

        if (!empty($course->id) && $course->coordinator === BaseService::getInstance()->getCurrentUser()->id) {
            return array("get","element","save");
        }
        return array("get","element");
    }

    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        // @codingStandardsIgnoreEnd
        $profileId = BaseService::getInstance()->getCurrentProfileId();
        $res = parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
        $course = new Course();
        $courses = $course->Find("1=1", array());

        $clist = array();
        foreach ($courses as $v) {
            $clist[$v->id] = $v;
        }

        $newRes = array();

        foreach ($res as $item) {
            $course = $clist[$item->course];
            if ($course->coordinator == $profileId) {
                $newRes[] = $item;
            }
        }

        return $newRes;
    }
}
