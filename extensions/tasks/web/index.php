<?php
$user = \Classes\BaseService::getInstance()->getCurrentUser();
echo "Welcome ".$user->username."<br/>";

echo "Creating a task <br/>";

$task = new \Tasks\Model\Task();
$taskName = 'Task-'.rand(rand(0, 100), 50000);

$task->name = $taskName;
$task->employee = $user->employee;
$task->description = $taskName.' description';
$task->created = date('Y-m-d H:i:s');
$task->updated = date('Y-m-d H:i:s');

/**
 * Saving the task, $ok will be false if there were any error during the creation
 */
$ok = $task->Save();

if (!$ok) {
    echo "Error: ".$task->ErrorMsg()." <br/>";
}

echo "Find last task <br/>";

$taskFromDB = new \Tasks\Model\Task();
/**
 * You can use load method to load the first matching task into an empty model
 */
$taskFromDB->Load('name = ?', [$taskName]);

var_dump($taskFromDB);