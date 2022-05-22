<?php

namespace Documents\Admin\Api;

use Classes\BaseService;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;
use Employees\Admin\Api\EmployeeCache;
use Employees\Common\Model\Employee;

class DocumentTaskCreator implements TaskCreator
{
    public function getTasksCreators()
    {
        $taskCreators = [];

        $user = BaseService::getInstance()->getCurrentUser();

        $taskCreators[] = function () {
            $expiredDocumentCount = $this->getMyExpiredDocumentCount();
            if ($expiredDocumentCount > 0) {
                return (new Task(
                    Task::PRIORITY_WARNING,
                    sprintf(
                        'You have %s expired documents',
                        $expiredDocumentCount
                    )
                ))->setLink(
                    CLIENT_BASE_URL.'?g=modules&n=documents&m=module_Documents',
                    'Check Documents'
                );
            }

            return null;
        };

        if ('Admin' === $user->user_level || 'Manager' === $user->user_level) {
            $taskCreators[] = function () {
                $expiredDocumentCount = $this->getSubordinateExpiredDocumentCount();
                if ($expiredDocumentCount > 0) {
                    return (new Task(
                        Task::PRIORITY_WARNING,
                        sprintf(
                            'Your subordinates have %s expired documents',
                            $expiredDocumentCount
                        )
                    ))->setLink(
                        CLIENT_BASE_URL.'?g=admin&n=documents&m=admin_Manage#tabEmployeeDocument',
                        'Manage Documents'
                    );
                }

                return null;
            };
        }


        return $taskCreators;
    }

    protected function getSubordinateExpiredDocumentCount()
    {
        $subIds = EmployeeCache::getSubordinateIds();
        if (empty($subIds)) {
            return 0;
        }

        $user = BaseService::getInstance()->getCurrentUser();

        if ('Admin' === $user->user_level) {
            $query = "select count(id) as c 
                    from EmployeeDocuments 
                    where employee IN (?) and valid_until < ?";
        } else {
            $query = "select count(id) as c 
                    from EmployeeDocuments 
                    where employee IN (?) and valid_until < ? and visible_to IN ('Manager', 'Owner')";
        }

        $rs = $user->DB()->Execute($query, [join(',', $subIds), date('Y-m-d')]);
        $count = $rs[0]['c'];

        return $count;
    }

    protected function getMyExpiredDocumentCount()
    {
        $user = BaseService::getInstance()->getCurrentUser();
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        if (empty($employee->id)) {
            return 0;
        }

        $query = "select count(id) as c 
                    from EmployeeDocuments 
                    where employee = ? and valid_until < ? and (visible_to = ? or visible_to = ?)";

        // $user->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        // TODO - sending notifications only for Owner documents, this need to be extended later
        $rs = $user->DB()->Execute($query, [$employee->id, date('Y-m-d'), 'Owner', 'Owner Only']);
        $count = $rs[0]['c'];

        return $count;
    }
}
