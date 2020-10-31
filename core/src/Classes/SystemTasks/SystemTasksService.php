<?php
namespace Classes\SystemTasks;

use Classes\SystemTasks\DTO\Task;

class SystemTasksService
{
    protected $taskCreators = [];

    private static $me = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new SystemTasksService();
        }

        return self::$me;
    }

    public function registerTaskCreator(TaskCreator $taskCreator)
    {
        $this->taskCreators[] = $taskCreator;
    }

    protected function prepareTaskCreatorCallbacks()
    {
        $taskGenerators = [];
        foreach ($this->taskCreators as $taskCreator) {
            $taskList = $taskCreator->getTasksCreators();
            foreach ($taskList as $order => $callback) {
                $nextOrder = $order * 1000;
                while (isset($taskGenerators[$nextOrder])) {
                    $nextOrder = 1 + $nextOrder;
                }

                $taskGenerators[$nextOrder] = $callback;
            }
        }

        return $taskGenerators;
    }
    public function getAdminTasks()
    {
        $tasks = [];
        $taskGenerators = $this->prepareTaskCreatorCallbacks();
        ksort($taskGenerators);
        foreach ($taskGenerators as $key => $callback) {
            $task = $callback();
            if (!empty($task)) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }
}
