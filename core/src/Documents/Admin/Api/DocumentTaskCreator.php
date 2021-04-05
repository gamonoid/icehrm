<?php

namespace Documents\Admin\Api;

use Classes\BaseService;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;
use Employees\Common\Model\Employee;

class DocumentTaskCreator implements TaskCreator
{
    public function getTasksCreators()
    {
        $taskCreators = [];

        $taskCreators[1] = function () {
            $expiredDocumentCount = $this->getMyExpiredDocumentCount();
            if ($expiredDocumentCount > 0) {
                (new Task(
                    Task::PRIORITY_ERROR,
                    sprintf(
                        'You have %s expired documents %s',
                        $expiredDocumentCount,
                        $expiredDocumentCount > 0 ? 's' : ''
                    )
                ))->setLink(
                    CLIENT_BASE_URL.'?g=modules&n=documents&m=module_Documents',
                    'Check Documents'
                );
            }

            return null;
        };

        return $taskCreators;
    }

    protected function getMyExpiredDocumentCount()
    {
        $user = BaseService::getInstance()->getCurrentUser();
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        if (empty($employee->id)) {
            return 0;
        }

        $query = "select count(id) as c from EmployeeDocuments where employee = ? and valid_until < ? and visible_to = ?";

        $user->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        // TODO - sending notifications only for Owner documents, this need to be extended later
        $rs = $user->DB()->Execute($query, [$employee->id, date('Y-m-d'), 'Owner']);
        $count = $rs->fields['c'];

        return $count;
    }
}
