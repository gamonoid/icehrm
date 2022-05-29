<?php


namespace Settings\Admin\Api;

use Classes\BaseService;
use Classes\SettingsManager;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;

class SettingTaskCreator implements TaskCreator
{

    public function getTasksCreators()
    {
        $taskCreators = [];

        $user = BaseService::getInstance()->getCurrentUser();

        if ('Admin' === $user->user_level) {
            $taskCreators[] = function () {
                $companyName = SettingsManager::getInstance()->getSetting('Company: Name');
                if ('Sample Company Pvt Ltd' === $companyName) {
                    return (new Task(Task::PRIORITY_WARNING, 'Your company name and description needs to be updated'))
                        ->setLink(
                            CLIENT_BASE_URL.'?g=admin&n=settings&m=admin_System',
                            'Goto Settings'
                        );
                }

                return null;
            };

            $taskCreators[] = function () {
                $emailFrom = SettingsManager::getInstance()->getSetting('Email: Email From');
                if ('icehrm@mydomain.com' === $emailFrom) {
                    return (new Task(Task::PRIORITY_WARNING, 'Default email settings are not updated'))
                        ->setLink(
                            CLIENT_BASE_URL.'?g=admin&n=settings&m=admin_System#tabEmailSetting',
                            'Update email settings'
                        );
                }

                return null;
            };
        }

        return $taskCreators;
    }
}
